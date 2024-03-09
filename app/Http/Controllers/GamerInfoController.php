<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionDay;

class GamerInfoController extends Controller
{
    public function index()
    {
        $actual_day = CompetitionDay::actual_day();
        $compos = $actual_day->competitions;

        $awaiting_gamers = [];

        foreach($compos as $compo){
            $game_stations = $compo->game->game_stations;
            foreach ($game_stations as $game_station){
                $match = $game_station->actual_match();
                if (!is_null($match) && $match->status == "waiting"){
                    foreach($match->participations as $p){
                        $awaiting_gamers[] = $p->gamer->nickname;
                    }
                }
            }
        }

        return view('gamerinfo', compact('actual_day', 'awaiting_gamers'));
    }
}
