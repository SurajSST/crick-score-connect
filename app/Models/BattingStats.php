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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the relationship with Match model for the match of the batting stats
    public function match()
    {
        return $this->belongsTo(Matches::class, 'match_id');
    }

    // Define the relationship with Innings model for the innings of the batting stats
    public function innings()
    {
        return $this->belongsTo(Innings::class, 'innings_id');
    }
}
