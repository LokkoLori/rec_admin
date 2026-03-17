<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Competition;

class ObsController extends Controller
{
    public function bracketView()
    {
        $competition = \App\Models\Competition::where('final_status', 'active')->first();

        $matches = [];
        $activeMatchIndex = null;
        $podium = [
            'first' => null,
            'second' => null,
            'third' => null,
        ];

        if ($competition) {
            // Unpack the array directly with the new key
            ['matches' => $matches, 'activeMatchIndex' => $activeMatchIndex] = $competition->getFinalsBracket();

            // Determine 3rd place if Bronze match (index 6) is finished
            if (isset($matches[6]) && $matches[6]->status === 'finished' && $matches[6]->participations->count() > 0) {
                $p1 = $matches[6]->participations[0];
                $p2 = $matches[6]->participations[1] ?? null;

                if ($p2) {
                    $podium['third'] = ($p1->score > $p2->score) ? $p1->gamer : $p2->gamer;
                } else {
                    $podium['third'] = $p1->gamer; // BYE case
                }
            }

            // Determine 1st and 2nd place if Final match (index 7) is finished
            if (isset($matches[7]) && $matches[7]->status === 'finished' && $matches[7]->participations->count() > 0) {
                $p1 = $matches[7]->participations[0];
                $p2 = $matches[7]->participations[1] ?? null;

                if ($p2) {
                    if ($p1->score > $p2->score) {
                        $podium['first'] = $p1->gamer;
                        $podium['second'] = $p2->gamer;
                    } else {
                        $podium['first'] = $p2->gamer;
                        $podium['second'] = $p1->gamer;
                    }
                } else {
                    $podium['first'] = $p1->gamer; // BYE case
                }
            }
        }

        // Return to the OBS view
        return view('obs.bracket', compact('competition', 'matches', 'activeMatchIndex', 'podium'));
    }

    public function gameView()
    {
        // Get active competition with its day
        $competition = Competition::with('competitionDay')
            ->where('final_status', 'active')
            ->first();

        $activeMatch = null;

        if ($competition) {
            // Fetch only the currently started match
            $activeMatch = \App\Models\GameMatch::with(['participations.gamer'])
                ->where('competition_id', $competition->id)
                ->where('status', 'started')
                ->first();
        }

        return view('obs.game', compact('competition', 'activeMatch'));
    }
}