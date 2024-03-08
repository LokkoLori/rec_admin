<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function actual_competition(){
        // in theory only one active competition for a game should be
        return Competition::where("competition_day_id", CompetitionDay::actual_day()->id)->where("game_id", $this->id)->first();
    }
}
