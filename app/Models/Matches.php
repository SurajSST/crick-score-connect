<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    use HasFactory;

    protected $fillable = [
        'team1_id',
        'team2_id',
        'date',
        'time',
        'match_result',
        'toss_winner_id',
        'venue',
        'overs',
        'players_per_team',
    ];
}
