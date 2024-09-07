<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    use HasFactory;

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function participations()
    {
        return $this->hasMany(GameMatchParticipation::class);
    }

    public function game_station()
    {
        return $this->belongsTo(GameStation::class, 'game_station_id');
    }

    public static function draw_match(GameStation $game_station){

        if ($game_station->available != 1) {
            return null;
        }

        $lockFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "match_draw.lock";
        $lockFile = fopen($lockFilePath, "w+");


        if (!flock($lockFile, LOCK_EX)) {
            return null;
        }

        $game_match = null;

        try {

            $competition = $game_station->game->actual_competition();

            $free_gamers_table = $competition->free_gamers_table();

            if (count($free_gamers_table) < 2){
                return null;
            }

            $rc = $competition->round_count;
            $p1i = 0;
            $p2i = 0;

            // find the first available gamer pair in the priority list
            while($p1i < count($free_gamers_table)-1){


                if ($rc <= $free_gamers_table[$p1i]["match_count"]){
                    # if we're running out from gamers who hasn't ended compo
                    return null;
                }

                $available_opponents = $free_gamers_table[$p1i]["available_opponents"]; 
                $p2i = $p1i + 1;
                if (count($available_opponents) == 0){
                    // if all oppnent is finished the game, then choose the next free gamer, even if he has more than max matches
                    break;
                }

                while($p2i < count($free_gamers_table)){
                    if (in_array($free_gamers_table[$p2i]["gamer"], $available_opponents)){
                        break;
                    }
                    $p2i++;
                }

                if ($p2i < count($free_gamers_table)){
                    break;
                }
                $p1i++;
            }

            if ($p1i == count($free_gamers_table)-1){
                // there's no available pair on free users table
                return null;
            }

            $game_match = new GameMatch;
            $game_match->game_station_id = $game_station->id;
            $game_match->competition_id = $competition->id;
            $game_match->status = "waiting";
            $game_match->save();

            $participant_1st = new GameMatchParticipation;
            $participant_1st->game_match_id = $game_match->id;
            $participant_1st->gamer_id = $free_gamers_table[$p1i]["gamer"]->id;
            $participant_1st->save();

            $participant_1st = new GameMatchParticipation;
            $participant_1st->game_match_id = $game_match->id;
            $participant_1st->gamer_id = $free_gamers_table[$p2i]["gamer"]->id;
            $participant_1st->save();

        } finally {
            flock($lockFile, LOCK_UN);
            return $game_match;
        }
    }

    public function finish_with_result(int $score1, int $score2){
        $this->status = "finished";

        $p1 = $this->participations->get(0);
        $p1->score = $score1;
        if (is_null($p1->score)){
            $p1->score = 0;
        }
        $p1->save();

        $p2 = $this->participations->get(1);
        $p2->score = $score2;
        if (is_null($p2->score)){
            $p2->score = 0;
        }
        $p2->save();

        $this->save();
    }
}
