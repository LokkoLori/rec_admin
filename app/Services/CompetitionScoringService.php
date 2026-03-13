<?php

namespace App\Services;

use App\Models\CompetitionDay;

class CompetitionScoringService
{
    /**
     * Calculates and sorts the score tables for a given competition day.
     *
     * @param CompetitionDay $actual_day
     * @return array
     */
    public function getSortedScoreTables(CompetitionDay $actual_day): array
    {
        $compo_score_tables = [];
        $compos = $actual_day->competitions;
        
        foreach ($compos as $compo){
            
            $compo_data = [];
            $compo_data["compo"] = $compo;
            $compo_data["gamer_data"] = [];
            $entries = $compo->entries->whereIn('status', ['accepted', 'disqualified', 'revoked', 'finished']);
            
            $gamer_points = [];

            foreach($entries as $entry){
                $gamer_data = [];

                $gamer = $entry->gamer;
                $gamer_data["gamer"] = $gamer;

                $participations = $gamer->finished_qlf_matches($compo);
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

                $gamer_data["qualified"] = 1;
                if (in_array($entry->status, ["disqualified", "revoked"])){
                    $gamer_data["qualified"] = 0;
                }
                $gamer_data["points"] = $entry->points;
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


        $aggregated_gamers = [];

        foreach ($compo_score_tables as $table) {
            foreach ($table["gamer_data"] as $gamer_data) {
                $gamer_id = $gamer_data["gamer"]->id;

                if (!isset($aggregated_gamers[$gamer_id])) {
                    // Initialize the gamer's summary record
                    $aggregated_gamers[$gamer_id] = [
                        "gamer"           => $gamer_data["gamer"],
                        "qualified"       => 1, // Assuming they are qualified overall if they appear here
                        "points"          => 0,
                        "primary_score"   => 0,
                        "secondary_score" => 0,
                        "matches"         => [] // Likely not needed in the summary view, but keeps the structure intact
                    ];
                }

                // Sum up the metrics
                $aggregated_gamers[$gamer_id]["points"] += $gamer_data["points"];
                $aggregated_gamers[$gamer_id]["primary_score"] += $gamer_data["primary_score"];
                $aggregated_gamers[$gamer_id]["secondary_score"] += $gamer_data["secondary_score"];
                array_push($aggregated_gamers[$gamer_id]["matches"], ...$gamer_data["matches"]);
            }
        }

        // 2. Emulate the Game and Competition models
        $summary_game = new \App\Models\Game();
        $summary_game->name = "Combined compo"; // This is what $table["compo"]->game->name will output

        $summary_compo = new \App\Models\Competition();
        // Manually injecting the relationship so Laravel doesn't try to query the database
        $summary_compo->setRelation('game', $summary_game);

        // 3. Create the summary table structure
        $summary_table = [
            "compo"      => $summary_compo,
            "gamer_data" => array_values($aggregated_gamers)
        ];

        // 5. Prepend or append the summary table to the main array
        // Using array_unshift puts it at the very top of the page. Use $compo_score_tables[] = to put it at the bottom.
        $compo_score_tables[] = $summary_table;

        foreach($compo_score_tables as &$table){
            
            // sorting score tables by logic!

            usort($table["gamer_data"], function ($a, $b) {

                if ($a["qualified"] != $b["qualified"]){
                    return $a["qualified"] < $b["qualified"] ? 1 : -1;
                }

                if ($a["points"] != $b["points"]){
                    return $a["points"] < $b["points"] ? 1 : -1;
                }

                if ($a["primary_score"] != $b["primary_score"]){
                    return $a["primary_score"] < $b["primary_score"] ? 1 : -1;
                }

                return $a["secondary_score"] < $b["secondary_score"] ? 1 : -1;
            });

        }

        return $compo_score_tables;
    }
}