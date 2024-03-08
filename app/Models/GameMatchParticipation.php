<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMatchParticipation extends Model
{
    use HasFactory;

    public function gamer()
    {
        return $this->belongsTo('App\Models\Gamer', 'gamer_id');
    }

    public function game_match()
    {
        return $this->belongsTo('App\Models\GameMatch', 'game_match_id');
    }

    public function competition()
    {
        return $this->game_match->competition;
    }

    public function opponent()
    {
        return $this->game_match->participations->where('id', '!=', $this->id)->first()->gamer;
    }
}
