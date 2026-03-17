<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompetitionDay;
use App\Models\Competition;
use App\Services\CompetitionScoringService;
use App\Models\GameMatch;
use App\Models\GameMatchParticipation;
use App\Models\Entry;
use App\Models\GameStation;

class FinalsController extends Controller
{   

    public function index()
    {
        $actual_day = CompetitionDay::actual_day();

        // Get all competitions for today
        $competitions = Competition::where('competition_day_id', $actual_day->id)->get();

        // Get the IDs of today's competitions
        $compo_ids = $competitions->pluck('id');

        // Check if any final matches exist for today's competitions
        $setupDone = GameMatch::whereIn('competition_id', $compo_ids)
            ->where('type', 'fnl')
            ->exists();

        return view('finals.index', compact('competitions', 'setupDone'));
    }

    public function startCompetition(Competition $competition)
    {
        // Safety check: reset any currently active competitions on the same day to 'pending'
        // This ensures only one competition is active at any given time.
        Competition::where('competition_day_id', $competition->competition_day_id)
            ->where('final_status', 'active')
            ->update(['final_status' => 'pending']);

        // Set the requested competition to active
        $competition->final_status = 'active';
        $competition->save();

        // Find the first quarter-final match and start it
        $firstMatch = GameMatch::where('competition_id', $competition->id)
            ->where('type', 'qfn')
            ->orderBy('id', 'asc')
            ->first();

        $firstMatch->status = 'started';
        $firstMatch->save();

        return redirect()->route('finals.index')->with('success', "{$competition->game->name} finals are now ACTIVE!");
    }

    public function manageMatches(\App\Models\Competition $competition)
    {
        // 1. Try to find an already active match
        $activeMatch = GameMatch::with(['participations.gamer'])
            ->where('competition_id', $competition->id)
            ->whereIn('type', ['qfn', 'sfn', 'brz', 'fnl'])
            ->where('status', 'started')
            ->first();

        // 2. If no active match, find the next pending one and auto-start it
        if (!$activeMatch) {
            $activeMatch = GameMatch::with(['participations.gamer'])
                ->where('competition_id', $competition->id)
                ->whereIn('type', ['qfn', 'sfn', 'brz', 'fnl'])
                ->where('status', 'waiting')
                ->orderBy('id', 'asc')
                ->first();

            if ($activeMatch) {
                $activeMatch->status = 'started';
                $activeMatch->save();
            }
        }

        return view('finals.manage', compact('competition', 'activeMatch'));
    }

    public function finishMatch(Request $request, GameMatch $gameMatch)
    {
        $competition_id = $gameMatch->competition_id;

        // Fetch ALL matches for this competition in creation order to establish the indices (0 to 7)
        $allMatches = GameMatch::where('competition_id', $competition_id)
            ->whereIn('type', ['qfn', 'sfn', 'brz', 'fnl'])
            ->orderBy('id', 'asc')
            ->get();

        // Find the index of the current match (0 to 7)
        $currentIndex = $allMatches->search(function($m) use ($gameMatch) {
            return $m->id == $gameMatch->id;
        });

        // Your exact progression logic mapped by index
        $progressionGraph = [
            0 => ['winner_to' => 4], // QF1 -> SF1
            1 => ['winner_to' => 5], // QF2 -> SF2
            2 => ['winner_to' => 5], // QF3 -> SF2
            3 => ['winner_to' => 4], // QF4 -> SF1
            4 => ['winner_to' => 7, 'loser_to' => 6], // SF1 -> Final / Bronze
            5 => ['winner_to' => 7, 'loser_to' => 6], // SF2 -> Final / Bronze
            6 => [], // Bronze -> End
            7 => []  // Final -> End
        ];

        $routes = $progressionGraph[$currentIndex] ?? [];

        $participations = $gameMatch->participations;

        $winner_id = null;
        $loser_id = null;

        $p1 = $participations[0];
        $p2 = $participations[1];

        if ($p1->score > $p2->score) {
            $winner_id = $p1->gamer_id;
            $loser_id = $p2->gamer_id;
        } elseif ($p2->score > $p1->score) {
            $winner_id = $p2->gamer_id;
            $loser_id = $p1->gamer_id;
        } else {
            // Prevent finishing a match that is tied
            return redirect()->back()->with('error', 'Cannot finish a match with a tied score!');
        }

        // 2. Award Championship Points to the Winner
        if ($winner_id) {
            $pointsToAdd = 0;
            switch ($gameMatch->type) {
                case 'qfn': $pointsToAdd = 1; break;
                case 'brz': $pointsToAdd = 2; break;
                case 'sfn': $pointsToAdd = 3; break;
                case 'fnl': $pointsToAdd = 4; break;
            }

            if ($pointsToAdd > 0) {
                Entry::where('competition_id', $competition_id)
                    ->where('gamer_id', $winner_id)
                    ->increment('points', $pointsToAdd);
            }
        }

        // Advance Winner
        if (isset($routes['winner_to'])) {
            $nextMatchForWinner = $allMatches[$routes['winner_to']];
            
            $part = new GameMatchParticipation();
            $part->game_match_id = $nextMatchForWinner->id;
            $part->gamer_id = $winner_id;
            $part->score = 0;
            $part->save();
        }

        // Advance Loser (only applies to Semi-Finals going to Bronze)
        if (isset($routes['loser_to'])) {
            $nextMatchForLoser = $allMatches[$routes['loser_to']];
            
            $part = new GameMatchParticipation();
            $part->game_match_id = $nextMatchForLoser->id;
            $part->gamer_id = $loser_id;
            $part->score = 0;
            $part->save();
        }

        // Close the current match
        $gameMatch->status = 'finished';
        $gameMatch->save();

        return redirect()->back()->with('success', 'Match finished, players advanced.');
    }

    public function setup(Request $request, CompetitionScoringService $scoringService)
    {
        $actual_day = CompetitionDay::actual_day();

        $compo_score_tables = $scoringService->getSortedScoreTables($actual_day);

        
        // Validation phase: Check if all qualified gamers have played the required number of matches
        foreach ($compo_score_tables as $key => $table) {
            $compo = $table["compo"];

            // Remove the combined compo from the array, we don't need bracket for it
            if (str_starts_with($compo->game->name, 'Combined')) {
                unset($compo_score_tables[$key]);
                continue;
            }

            // Skip if finals are already generated for this competition
            $has_finals = GameMatch::where('competition_id', $compo->id)
                ->where('type', 'fnl')
                ->exists();

            if ($has_finals) {
                unset($compo_score_tables[$key]);
                continue;
            }

            $required_matches = $compo->round_count;

            foreach ($table["gamer_data"] as $gamer_data) {
                // Check if gamer is qualified (not disqualified/revoked) and has fewer matches than required
                if ($gamer_data["qualified"] == 1 && count($gamer_data["matches"]) < $required_matches) {
                    $nickname = $gamer_data["gamer"]->nickname;
                    $played = count($gamer_data["matches"]);
                    
                    return redirect()->route('finals.index')->with('error', "Generating interupted: {$nickname} has played only {$played}/{$required_matches} qualifing matches!");
                }
            }
        }


        foreach ($compo_score_tables as $table) {
            $compo = $table["compo"];
            $rank = 1;

            foreach ($table["gamer_data"] as $gamer_data) {
                // Skip disqualified gamers, they get nothing
                if ($gamer_data["qualified"] == 0) {
                    continue; 
                }

                // Top 8 gets 2 points, the rest gets 1 point
                $points = ($rank <= 8) ? 2 : 1;
                $gamer = $gamer_data["gamer"];

                // Update the entry table
                Entry::where('competition_id', $compo->id)
                    ->where('gamer_id', $gamer->id)
                    ->update(['points' => $points]);

                // Increment the rank only for qualified players
                $rank++;
            }
        }


        foreach ($compo_score_tables as $table) {
            $compo = $table["compo"];
            
            // Find the first available game station for this game
            $station = GameStation::where('game_id', $compo->game_id)->first();
            
            if (!$station) {
                return redirect()->route('finals.index')->with('error', "Setup aborted: No game station found for game {$compo->game->name}");
            }
            

            $gamer_data = $table["gamer_data"];

            // Skip compo if there are not enough players to form at least one match
            if (count($gamer_data) < 8) {
                continue; 
            }

            // 1. Generate Quarter-Finals (qfn)
            $qf_matchups = [
                [0, 7], // 1st vs 8th
                [1, 6], // 2nd vs 7th
                [2, 5], // 3rd vs 6th
                [3, 4]  // 4th vs 5th
            ];

            foreach ($qf_matchups as $matchup) {
                $match = new GameMatch();
                $match->competition_id = $compo->id;
                $match->game_station_id = $station->id;
                $match->type = 'qfn';
                $match->save();

                // Add participants only if they exist (handles cases with fewer than 8 players)
                foreach ($matchup as $index) {
                    if (isset($gamer_data[$index])) {
                        $part = new GameMatchParticipation();
                        $part->game_match_id = $match->id;
                        $part->gamer_id = $gamer_data[$index]["gamer"]->id;
                        $part->score = 0;
                        $part->save();
                    }
                }
            }

            // 2. Generate Semi-Finals (sfn)
            for ($i = 0; $i < 2; $i++) {
                $match = new GameMatch();
                $match->competition_id = $compo->id;
                $match->game_station_id = $station->id;
                $match->type = 'sfn';
                $match->save();
            }

            // 3. Generate Bronze Match (brz)
            $bronze = new GameMatch();
            $bronze->competition_id = $compo->id;
            $bronze->game_station_id = $station->id;
            $bronze->type = 'brz';
            $bronze->save();

            // 4. Generate Final Match (fnl)
            $final = new GameMatch();
            $final->competition_id = $compo->id;
            $final->game_station_id = $station->id;
            $final->type = 'fnl';
            $final->save();
        }

        // Temporary return to test this specific phase
        return redirect()->route('finals.index')->with('success', 'Finals setup is OK!');
    }

    public function updateScore(Request $request, GameMatchParticipation $participation)
    {
        $action = $request->input('action');

        if ($action === 'increase') {
            $participation->score++;
        } elseif ($action === 'decrease') {
            // Prevent negative scores
            if ($participation->score > 0) {
                $participation->score--;
            }
        }

        $participation->save();

        // Redirect back so the admin stays on the current page
        return redirect()->back();
    }

    public function closeCompetition(Request $request, Competition $competition)
    {
        $competition->final_status = 'finished';
        $competition->save();

        return redirect()->route('finals.index')->with('success', 'Competition closed successfully. OBS screens are now cleared.');
    }
}