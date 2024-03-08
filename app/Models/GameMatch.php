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
}
