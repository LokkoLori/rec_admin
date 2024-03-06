<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionDay extends Model
{
    use HasFactory;

    protected $fillable = ['season_id', 'date', 'name'];
}
