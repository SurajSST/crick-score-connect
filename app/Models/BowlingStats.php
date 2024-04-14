<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BowlingStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_id',
        'innings_id',
        'overs_bowled',
        'runs_conceded',
        'wickets_taken',
        'maidens',
        'economy_rate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function match()
    {
        return $this->belongsTo(Matches::class, 'match_id');
    }
    public function innings()
    {
        return $this->belongsTo(Innings::class, 'innings_id');
    }
}
