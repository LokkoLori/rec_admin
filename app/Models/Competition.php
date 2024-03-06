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
}
