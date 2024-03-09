<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = ['competition_day_id', 'game_id'];

    public function competitionDay()
    {
        return $this->belongsTo(CompetitionDay::class, 'competition_day_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function entries()
    {
        return $this->hasMany(Entry::class)->where('status', 'accepted');
    }

    public function game_matches()
    {
        return $this->hasMany(GameMatch::class);
    }

    public function free_gamers_table()
    {

        // gives not busy gamers, their avilable partners, how many rounds they played
        $ret = [];

        $free_gamers = [];  // free gamers in this compo
        $available_opponents = [];
        foreach ($this->entries as $entry){
            $gamer =  $entry->gamer;
            if ($gamer->finished_participations($this)->count() < $this->round_count){
                $available_opponents[] = $gamer;
            }
            if (!$gamer->is_busy()){
                $free_gamers[] = $gamer;
            }
        }

        foreach ($free_gamers as $gamer){
            $row = [];

            $row["gamer"] = $gamer;
            $participations = $gamer->finished_participations($this);
            $row["match_count"] = $participations->count();

            $exluded_opponents = [$gamer]; // it's a life hack!

            foreach ($participations as $p){
                $exluded_opponents[] = $p->opponent();
            }

            $row["available_opponents"] = array_diff($available_opponents, $exluded_opponents);
            $row["random"] = random_int(0, 10000);
        
            $ret[] = $row;
        }

        usort($ret, function ($a, $b) {
            if ($a["match_count"] != $b["match_count"]) {
                return $a["match_count"] < $b["match_count"] ? -1 : 1;
            }
        
            if (count($a["available_opponents"]) != count($a["available_opponents"]) ) {
                return count($a["available_opponents"]) < count($a["available_opponents"]) ? -1 : 1;
            }
        
            return $a["random"] < $b['random'] ? -1 : 1;
        });

        return $ret;
    }
}
