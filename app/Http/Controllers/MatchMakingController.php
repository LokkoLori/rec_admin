<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Models\GameStation;
use App\Models\GameMatch;
use App\Models\GameMatchParticipation;
use App\Models\CompetitionDay;

class MatchMakingController extends Controller
{
    public function index(Request $request)
    {
        $actual_day = CompetitionDay::actual_day();
        $game_stations = GameStation::all();

        $competitions = $actual_day->competitions;

        $free_gamers_tables = [];
        $finished_matches = $actual_day->game_matches()->where('status', 'finished')->sortByDesc('id');;

        foreach ($competitions as $competition){
            $free_gamers_tables[$competition->id] = $competition->free_gamers_table();
        }

        return view('matchmaking.index',  compact('game_stations', 'actual_day', 'free_gamers_tables', 'finished_matches'));
    }

    public function create_match(Request $request)
    {
        $game_station = GameStation::find($request->input("game_station_id"));
        $competition = $game_station->game->actual_competition();

        $free_gamers_table = $competition->free_gamers_table();

        if (count($free_gamers_table) < 2){
            return redirect()->route('matchmaking.index');
        }

        $p1i = 0;
        $p2i = 0;

        // find the first available gamer pair in the priority list
        while($p1i < count($free_gamers_table)-1){
            $available_opponents = $free_gamers_table[$p1i]["available_opponents"]; 
            $p2i = $p2i + 1;
            if (count($available_opponents) == 0){
                // if all oppnent is finished the game, then choose the next free gamer, even if he has more than max matches
                break;
            }
            while($p2i < count($free_gamers_table)){
                if (in_array($free_gamers_table[$p2i]["gamer"], $available_opponents)){
                    break;
                }
                $p2i++;
            }

            if ($p2i < count($free_gamers_table)){
                break;
            }
            $p1i++;
        }

        if ($p1i == count($free_gamers_table)-1){
            // there's no available pair on free users table
            return redirect()->route('matchmaking.index');
        }

        $game_match = new GameMatch;
        $game_match->game_station_id = $game_station->id;
        $game_match->competition_id = $competition->id;
        $game_match->status = "waiting";
        $game_match->save();

        $participant_1st = new GameMatchParticipation;
        $participant_1st->game_match_id = $game_match->id;
        $participant_1st->gamer_id = $free_gamers_table[0]["gamer"]->id;
        $participant_1st->save();

        $participant_1st = new GameMatchParticipation;
        $participant_1st->game_match_id = $game_match->id;
        $participant_1st->gamer_id = $free_gamers_table[1]["gamer"]->id;
        $participant_1st->save();

        return redirect()->route('matchmaking.index');
    }

    public function start_match(Request $request)
    {
        
        $game_match = GameMatch::find($request->input("game_match_id"));
        
        if ($request->input("match_action") == "start"){
            $game_match->status = "started";
        } elseif ($request->input("match_action") == "update") {
            if ($request->input("sub_gamer_1_id") != "0"){
                $p1 = $game_match->participations->get(0);
                $p1->gamer_id = $request->input("sub_gamer_1_id");
                $p1->save();
            }
            if ($request->input("sub_gamer_2_id") != "0"){
                $p2 = $game_match->participations->get(1);
                $p2->gamer_id = $request->input("sub_gamer_2_id");
                $p2->save();
            }
        } elseif ($request->input("match_action") == "cancel") {
            $game_match->status = "cancelled";
        } 

        $game_match->save();

        return redirect()->route('matchmaking.index');
    }

    public function finish_match(Request $request)
    {
        
        $game_match = GameMatch::find($request->input("game_match_id"));

        if ($request->input("match_action") == "finish"){
            $game_match->status = "finished";

            $p1 = $game_match->participations->get(0);
            $p1->score = $request->input("point_1");
            $p1->save();
    
            $p2 = $game_match->participations->get(1);
            $p2->score = $request->input("point_2");
            $p2->save();

        } elseif ($request->input("match_action") == "delete") {
            $game_match->status = "cancelled";
        }

        $game_match->save();

        return redirect()->route('matchmaking.index');
    }
}
