<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameStation extends Model
{

    public function actual_match()
    {
        // in theory only one active match should be for a gameStation
        return $this->hasMany(GameMatch::class, 'game_station_id')->whereIn('status', ['waiting', 'started'])->first();
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
    
    use HasFactory;
}
