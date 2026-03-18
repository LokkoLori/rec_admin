<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GameMatch;
use App\Models\Season;
use App\Models\CompetitionDay;
use App\Models\Game;
use App\Models\Gamer;

class MatchHistoryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Start building the query with eager loading
        // ALWAYS filter out test data: only include matches from active or finished Competition Days
        $query = GameMatch::with([
            'competition.game',
            'competition.competitionDay.season',
            'participations.gamer'
        ])
        ->whereHas('competition.competitionDay', function ($q) {
            // Adjust 'status' to your actual column name if it differs
            $q->whereIn('status', ['started', 'finished']);
        })
        ->orderBy('id', 'desc');

        // 2. Apply filters dynamically based on request parameters
        
        // Filter by Season
        if ($request->filled('season_id')) {
            $query->whereHas('competition.competitionDay', function ($q) use ($request) {
                $q->where('season_id', $request->season_id);
            });
        }

        // Filter by Competition Day
        if ($request->filled('competition_day_id')) {
            $query->whereHas('competition', function ($q) use ($request) {
                $q->where('competition_day_id', $request->competition_day_id);
            });
        }

        // Filter by Game
        if ($request->filled('game_id')) {
            $query->whereHas('competition', function ($q) use ($request) {
                $q->where('game_id', $request->game_id);
            });
        }

        // Filter by Gamer (must exist in the participations table for this match)
        if ($request->filled('gamer_id')) {
            $query->whereHas('participations', function ($q) use ($request) {
                $q->where('gamer_id', $request->gamer_id);
            });
        }

        // 3. Execute query with pagination (50 items per page)
        $matches = $query->paginate(50);

        // 4. Fetch data for the filter dropdowns
        $seasons = Season::orderBy('id', 'desc')->get();
        $days = CompetitionDay::orderBy('date', 'desc')->get();
        $games = Game::orderBy('name', 'asc')->get();
        $gamers = Gamer::orderBy('nickname', 'asc')->get();

        return view('matches.history', compact('matches', 'seasons', 'days', 'games', 'gamers'));
    }
}