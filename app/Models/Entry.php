<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = ['gamer_id', 'competition_id', 'note', 'points'];

    const STATUS_OPTIONS = ['applyed', 'accepted', 'revoked', 'disqualified', 'finished'];

    public function competition()
    {
        return $this->belongsTo('App\Models\Competition', 'competition_id');
    }

    public function gamer()
    {
        return $this->belongsTo('App\Models\Gamer', 'gamer_id');
    }
}
