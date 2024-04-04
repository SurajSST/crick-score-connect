<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BattingStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_id',
        'innings_id',
        'runs_scored',
        'fours',
        'sixes',
        'strike_rate',
        'balls_faced',
    ];
}
