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

    public function match()
    {
        return $this->belongsTo(Matches::class, 'match_id');
    }

    // Define the relationship with Team model for the batting team in the innings
    public function battingTeam()
    {
        return $this->belongsTo(Team::class, 'batting_team_id');
    }

    // Define the relationship with Team model for the bowling team in the innings
    public function bowlingTeam()
    {
        return $this->belongsTo(Team::class, 'bowling_team_id');
    }

    // Define the relationship with BattingStats model for batting stats of the innings
    public function battingStats()
    {
        return $this->hasMany(BattingStats::class);
    }

    // Define the relationship with BowlingStats model for bowling stats of the innings
    public function bowlingStats()
    {
        return $this->hasMany(BowlingStats::class);
    }
}
