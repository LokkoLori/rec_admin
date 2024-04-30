<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Http\Controllers\Controller;

use App\Models\GameStation;
use App\Models\GameMatch;

class MatchClientController extends Controller
{
    public function index(Request $request)
    {

        $game_station = GameStation::find($request->input("game_station_id"));

        $act_match = $game_station->actual_match();

        if (is_null($act_match )){
            $act_match = GameMatch::draw_match($game_station);
        }
        
        return view('matchclient.index', compact('game_station', 'act_match'));
    }

    public function action(Request $request){

        $game_station = GameStation::find($request->input("game_station_id"));

        $act_match = $game_station->actual_match();

        if (is_null($act_match )){
            return $this->index($request);
        }

        if ($act_match->status == "waiting"){
            $act_match->status = "started";
            $act_match->save();
        } elseif ($act_match->status == "started" && !is_null($request->input("match_result"))){            
            $scores = explode(':', $request->input("match_result"));
            $act_match->finish_with_result($scores[0], $scores[1]);
        }

        return $this->index($request);
    }
}
