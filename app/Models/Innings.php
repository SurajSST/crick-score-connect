<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Innings extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'batting_team_id',
        'bowling_team_id',
        'innings_number',
    ];
}
