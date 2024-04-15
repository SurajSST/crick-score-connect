<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamPlayer;
use App\Models\Matches;
use App\Models\BattingStats;
use App\Models\BowlingStats;

class DatabaseFactory
{
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teamPlayer()
    {
        return $this->belongsTo(TeamPlayer::class);
    }

    public function matches()
    {
        return $this->belongsTo(Matches::class);
    }

    public function battingStats()
    {
        return $this->belongsTo(BattingStats::class);
    }

    public function bowlingStats()
    {
        return $this->belongsTo(BowlingStats::class);
    }
}
