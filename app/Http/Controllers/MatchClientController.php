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
        
        return view('matchclient.index', compact('act_match'));
    }
}
