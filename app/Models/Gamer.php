<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gamer extends Model
{
    use HasFactory;

    protected $fillable = ['nickname', 'u14', 'women'];

    protected $casts = [
        'u14' => 'boolean',
        'women' => 'boolean',
    ];

    public function is_busy(int $cooldown_seconds = 0)
    {
        $hasActiveMatch = GameMatchParticipation::where('gamer_id', $this->id)->whereHas('game_match', function ($query) {
            $query->whereIn('status', ['waiting', 'started']);
        })->exists();

        if ($hasActiveMatch) {
            return true;
        }

        // If no active match but cooldown is required, check recent participations
        // based on the parent game_match's updated_at, which is guaranteed to change.
        if ($cooldown_seconds > 0) {
            $isInCooldown = GameMatchParticipation::where('gamer_id', $this->id)
                ->whereHas('game_match', function ($query) use ($cooldown_seconds) {
                    $query->where('updated_at', '>', now()->subSeconds($cooldown_seconds));
                })
                ->exists();

            return $isInCooldown;
        }

        return false;
    }

    public function finished_qlf_matches($competition)
    {
        return GameMatchParticipation::whereHas('game_match', function ($query) use ($competition) {
            $query->where('competition_id', $competition->id)
                  ->where('status', 'finished')
                  ->where('type', 'qlf');
        })->where('gamer_id', $this->id)->get();
    }
}
