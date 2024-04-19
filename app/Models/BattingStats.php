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
        'is_striker',
        'out', // Added missing field
        'is_non_striker', // Added missing field
        'is_bencher', // Added missing field
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
