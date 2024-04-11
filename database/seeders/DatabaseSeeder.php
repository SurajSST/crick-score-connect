<?php

namespace Database\Seeders;

use App\Models\BattingStats;
use App\Models\BowlingStats;
use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\Innings;
use App\Models\Matches;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create();

        Team::factory(10)->create();

        Matches::factory(5)->create();
        Innings::factory(10)->create();
        FriendRequest::factory(10)->create();
        Friendship::factory(5)->create();
        BattingStats::factory(10)->create();
        BowlingStats::factory(10)->create();
    }
}
