<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CompetitionDay;

class ScoreTableController extends Controller
{
    public function index()
    {
        $actual_day = CompetitionDay::actual_day();
        $compo_score_tables = []; 
        $compos = $actual_day->competitions;
        
        foreach ($compos as $compo){
            
            $compo_data = [];
            $compo_data["compo"] = $compo;
            $compo_data["gamer_data"] = [];
            $entries = $compo->entries->whereIn('status', ['accepted', 'finished']);
            
            $gamer_points = [];

            foreach($entries as $entry){
                $gamer_data = [];

                $gamer = $entry->gamer;
                $gamer_data["gamer"] = $gamer;

                $participations = $gamer->finished_participations($compo);
                $gamer_data["matches"] = [];
                $sum_score = 0;
                foreach($participations as $participation){
                    $match_data = [];
                    $match_data["score"] = $participation->score;
                    $match_data["opponent"] = $participation->opponent()->nickname;
                    $sum_score +=  $match_data["score"];
                    $gamer_data["matches"][] = $match_data;
                }

                $gamer_data["primary_score"] = $sum_score;
                $gamer_points[$gamer->nickname] = $sum_score;

                $compo_data["gamer_data"][] = $gamer_data;
            }

            foreach($compo_data["gamer_data"] as &$updating_gamer_data){
                
                $sum_sec_score = 0;
                foreach($updating_gamer_data["matches"] as $match){
                    $sum_sec_score += $match["score"] * $gamer_points[$match["opponent"]];
                }

                $updating_gamer_data["secondary_score"] = $sum_sec_score;
            }

            $compo_score_tables[] = $compo_data;
        }

        foreach($compo_score_tables as &$table){
            
            // sorting score tables by logic!

            usort($table["gamer_data"], function ($a, $b) {
                if ($a["primary_score"] != $b["primary_score"]){
                    return $a["primary_score"] < $b["primary_score"] ? 1 : -1;
                }

                return $a["secondary_score"] < $b["secondary_score"] ? 1 : -1;
            });

        }

        return view('scoretable', compact('actual_day', 'compo_score_tables'));
    }
}
