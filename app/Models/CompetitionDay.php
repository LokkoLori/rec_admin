<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionDay extends Model
{
    use HasFactory;

    protected $fillable = ['season_id', 'date', 'name'];

    public static function actual_day(){
        return CompetitionDay::where('status', 'started')->first();
    }

    public function season(){
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function competitions(){
        return $this->hasMany(Competition::class);
    }

    public function game_matches(){
        return $this->competitions()->with('game_matches')->get()->pluck('game_matches')->flatten(); 
    }
}
