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
        'key',
        'toss_winner_id',
        'toss_winner_batting_first',
        'venue',
        'overs',
        'players_per_team',
        'isGameFinished',
        'finishedMessage',
        'isGameCanceled',
        'user_id',
        'target',
        'CRR',
        'RRR',
        'extras',
        'first_inning_total_run',
        'first_inning_total_over',
        'first_inning_total_wicket',
        'second_inning_total_run',
        'second_inning_total_over',
        'second_inning_total_wicket',
    ];

    protected $casts = [
        'extras' => 'array',
    ];
    public function teams()
    {
        return $this->belongsTo(Team::class);
    }
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
