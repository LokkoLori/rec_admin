<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionDay;
use App\Models\GameMatch;
use App\Models\GameMatchParticipation;

class MatchesController extends Controller
{
    public function index(Request $request)
    {
        if (is_null($request->input("competion_day_id"))){
            $actual_day = CompetitionDay::actual_day();
        } else {
            $actual_day = CompetitionDay::find($request->input("competion_day_id"));
        }
        $finished_matches = $actual_day->game_matches()->where('status', 'finished')->sortByDesc('id');

        return view('matches',  compact('finished_matches'));
    }

    public function update(Request $request)
    {
        if ($request->input("match_action") == "update") {
            $p0 = GameMatchParticipation::find($request->input("participation_0_id"));
            $p1 = GameMatchParticipation::find($request->input("participation_1_id"));

            $p0->score = $request->input("participation_0_score");
            $p1->score = $request->input("participation_1_score");

            $p0->save();
            $p1->save();

        } elseif ($request->input("match_action") == "update") {
            $m = GameMatch::find($request->input("match_id"));
            $m->status = "cancelled";
            $m->save();
        }

        return redirect()->route('matches.index');
    }
}
