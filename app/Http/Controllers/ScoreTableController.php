<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionDay;
use App\Services\CompetitionScoringService;

class ScoreTableController extends Controller
{
    public function index(Request $request, CompetitionScoringService $scoringService)
    {
        if (is_null($request->input("competion_day_id"))){
            $actual_day = CompetitionDay::actual_day();
        } else {
            $actual_day = CompetitionDay::find($request->input("competion_day_id"));
        }
        
        // Calling the service
        $compo_score_tables = $scoringService->getSortedScoreTables($actual_day);

        $hide_combined = $request->boolean('hide_combined');

        return view('scoretable', compact('actual_day', 'compo_score_tables', 'hide_combined'));
    }
}
