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
        
        GameMatch::draw_match($game_station);

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
            if (is_null($p1->score)){
                $p1->score = 0;
            }
            $p1->save();
    
            $p2 = $game_match->participations->get(1);
            $p2->score = $request->input("point_2");
            if (is_null($p2->score)){
                $p2->score = 0;
            }
            $p2->save();

        } elseif ($request->input("match_action") == "delete") {
            $game_match->status = "cancelled";
        }

        $game_match->save();

        return redirect()->route('matchmaking.index');
    }
}
