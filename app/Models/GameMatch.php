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

        $competition = $game_station->game->actual_competition();

        $free_gamers_table = $competition->free_gamers_table();

        if (count($free_gamers_table) < 2){
            return null;
        }

        $p1i = 0;
        $p2i = 0;

        // find the first available gamer pair in the priority list
        while($p1i < count($free_gamers_table)-1){
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

        return $game_match;
    }
}
