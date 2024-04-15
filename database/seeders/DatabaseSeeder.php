<?php

namespace Database\Seeders;

use App\Models\BattingStats;
use App\Models\BowlingStats;
use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\Innings;
use App\Models\Matches;
use App\Models\Team;
use App\Models\TeamPlayer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // public function run(): void
    // {
    //     User::factory(100)->create();

    //     Team::factory(10)->create();
    //     TeamPlayer::factory()->times(10)->create();

    //     Matches::factory(5)->create();
    //     Innings::factory(10)->create();
    //     FriendRequest::factory(10)->create();
    //     Friendship::factory()->count(30)->create();
    //     BattingStats::factory(10)->create();
    //     BowlingStats::factory(10)->create();
    // }

    public function run()
    {
        \App\Models\User::factory(10)->create();
        \App\Models\Team::factory(5)->create();
        \App\Models\TeamPlayer::factory(50)->create();
        // \App\Models\Innings::factory(50)->create();
        \App\Models\Matches::factory(10)->create();
        \App\Models\BattingStats::factory(50)->create();
        \App\Models\BowlingStats::factory(50)->create();
    }
}
