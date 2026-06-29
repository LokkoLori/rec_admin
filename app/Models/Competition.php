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
        return $this->hasMany(Entry::class);
    }

    public function game_matches()
    {
        return $this->hasMany(GameMatch::class);
    }

    public function get_gamer_match_data()
    {
        $map = [];
        $entries = $this->entries->where('status', 'accepted');
        
        foreach ($entries as $entry) {
            $gamer = $entry->gamer;
            $participations = $gamer->finished_qlf_matches($this);
            
            $map[$gamer->id] = [
                'gamer' => $gamer,
                'participations' => $participations,
                'match_count' => $participations->count(),
                'points' => $participations->sum('score'),
            ];
        }
        
        return $map;
    }

    public function free_gamers_table()
    {

        // gives not busy gamers, their avilable partners, how many rounds they played
        $ret = [];

        $free_gamers = [];  // free gamers in this compo
        $available_opponents = [];

        // Build the map once, effectively killing the N+1 issue for this logic
        $gamer_data_map = $this->get_gamer_match_data();

        // Find the absolute minimum match count across ALL gamers (including busy ones)
        $min_match_count = 999999;
        foreach ($gamer_data_map as $data) {
            if ($data['match_count'] < $min_match_count) {
                $min_match_count = $data['match_count'];
            }
        }

        foreach ($gamer_data_map as $data) {
            $gamer =  $data['gamer'];
            $mc = $data['match_count'];
            
            if ($mc < $this->round_count){
                $available_opponents[] = $gamer;
            }
            
            // Filter: only add if not busy AND match count is at most min_match_count + 2
            if (!$gamer->is_busy(30) && $mc <= $min_match_count + 2){
                $free_gamers[] = $gamer;
            }
        }

        foreach ($free_gamers as $gamer){
            $row = [];

            // Pull pre-calculated data from our map
            $data = $gamer_data_map[$gamer->id];

            $row["gamer"] = $gamer;
            $participations = $data['participations'];
            $row["match_count"] = $data['match_count'];
            $row["points"] = $data['points'];
            
            $excluded_opponents = [$gamer]; // it's a life hack!

            foreach ($participations as $p){
                $excluded_opponents[] = $p->opponent();
            }

            $row["available_opponents"] = array_diff($available_opponents, $excluded_opponents);
            $row["random"] = random_int(0, 10000);
        
            $ret[] = $row;
        }

        usort($ret, function ($a, $b) {
            if ($a["match_count"] != $b["match_count"]) {
                return $a["match_count"] < $b["match_count"] ? -1 : 1;
            }
        
            if (count($a["available_opponents"]) != count($b["available_opponents"]) ) {
                return count($a["available_opponents"]) < count($b["available_opponents"]) ? -1 : 1;
            }
        
            return $a["random"] < $b['random'] ? -1 : 1;
        });

        return $ret;
    }

    /**
     * Get the mapped finals bracket and the currently active match index.
     *
     * @return array
     */
    public function getFinalsBracket(): array
    {
        $allMatches = \App\Models\GameMatch::with(['participations.gamer'])
            ->where('competition_id', $this->id)
            ->whereIn('type', ['qfn', 'sfn', 'brz', 'fnl'])
            ->orderBy('id', 'asc')
            ->get();

        $matches = [];
        $activeMatchIndex = null;

        // Map matches by their logical index (0-7)
        foreach ($allMatches as $index => $match) {
            $matches[$index] = $match;

            if ($match->status === 'started') {
                $activeMatchIndex = $index;
            }
        }

        return [
            'matches' => $matches,
            'activeMatchIndex' => $activeMatchIndex,
        ];
    }
}
