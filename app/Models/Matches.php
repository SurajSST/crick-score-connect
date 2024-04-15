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
    protected $casts = [
        'extras' => 'array',
    ];
    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    // Define the relationship with Team model for the second team in the match
    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    // Define the relationship with Team model for the toss winner team
    public function tossWinner()
    {
        return $this->belongsTo(Team::class, 'toss_winner_id');
    }

    // Define the relationship with Innings model for innings of the match
    public function innings()
    {
        return $this->hasMany(Innings::class);
    }
    public function battingStats()
    {
        return $this->hasMany(BattingStats::class, 'match_id');
    }
    public function bowlingStats()
    {
        return $this->hasMany(BowlingStats::class, 'match_id');
    }
}
