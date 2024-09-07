<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Season;

class ChampionshipController extends Controller
{
    public function index(Request $request)
    {
        if (is_null($request->input("season_id"))){
            $actual_season = Season::actual_season();
        } else {
            $actual_season = Season::find($request->input("season_id"));
        }

        $compos = [];

        $compo = [];
        $compo["table"] = DB::table('entries')
            ->join('gamers', 'entries.gamer_id', '=', 'gamers.id')
            ->join('competitions', 'entries.competition_id', '=', 'competitions.id')
            ->join('competition_days', 'competitions.competition_day_id', '=', 'competition_days.id')
            ->join('games', 'competitions.game_id', '=', 'games.id')
            ->where('competition_days.season_id', $actual_season->id)
            ->where('competition_days.status', '!=', 'cancelled')
            ->select(
                'gamers.nickname as gamer_name',
                DB::raw('SUM(entries.points) as total_points')
            )
            ->groupBy('gamers.nickname')
            ->orderBy('total_points', 'desc')
            ->get();
        
        $compo["name"] = "Combined Championship";

        $compos[] = $compo;

        $games = DB::table('games')
            ->join('competitions', 'games.id', '=', 'competitions.game_id')
            ->join('competition_days', 'competitions.competition_day_id', '=', 'competition_days.id')
            ->where('competition_days.season_id', $actual_season->id)
            ->where('competition_days.status', '!=', 'cancelled')
            ->select('games.id', 'games.name')
            ->distinct()
            ->get();

        foreach ($games as $game) {

            $compo = [];

            $compo["name"] = $game->name;

            $compo["table"] = DB::table('entries')
                ->join('gamers', 'entries.gamer_id', '=', 'gamers.id')
                ->join('competitions', 'entries.competition_id', '=', 'competitions.id')
                ->join('competition_days', 'competitions.competition_day_id', '=', 'competition_days.id')
                ->join('games', 'competitions.game_id', '=', 'games.id')
                ->where('competition_days.season_id', $actual_season->id)
                ->where('competition_days.status', '!=', 'cancelled')
                ->where('games.id', $game->id)
                ->select(
                    'gamers.nickname as gamer_name',
                    DB::raw('SUM(entries.points) as total_points')
                )
                ->groupBy('gamers.nickname')
                ->orderBy('total_points', 'desc')
                ->get();
            
            $compos[] = $compo;

        }

        return view('championship', compact('actual_season', 'compos'));
    }
}
