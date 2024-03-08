<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gamer extends Model
{
    use HasFactory;

    public function is_busy()
    {
        $hasActiveMatch = GameMatchParticipation::where('gamer_id', $this->id)->whereHas('game_match', function ($query) {
            $query->whereIn('status', ['waiting', 'started']);
        })->exists();

        return $hasActiveMatch;
    }

    public function finished_participations($competition)
    {
        return GameMatchParticipation::whereHas('game_match', function ($query) use ($competition) {
            $query->where('competition_id', $competition->id)->where('status', 'finished');;
        })->where('gamer_id', $this->id)->get();
    }
}
