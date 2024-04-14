<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Matches;
use App\Models\Innings;
use App\Models\BattingStats;
use Illuminate\Database\Eloquent\Factories\Factory;

class BattingStatsFactory extends Factory
{
    protected $model = BattingStats::class;

    public function definition()
    {
        $user = User::all()->random();
        $match = Matches::all()->random();
        $innings = Innings::all()->random();

        return [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'innings_id' => $innings->id,
            'runs_scored' => $this->faker->numberBetween(0, 200),
            'fours' => $this->faker->numberBetween(0, 50),
            'sixes' => $this->faker->numberBetween(0, 20),
            'strike_rate' => $this->faker->randomFloat(2, 50, 200),
            'balls_faced' => $this->faker->numberBetween(0, 300),
            'status' => 'bencher', // By default, the player is on the bench
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (BattingStats $battingStats) {
            $matchBattingStats = BattingStats::where('match_id', $battingStats->match_id)->get();

            if ($matchBattingStats->count() == 1) {
                $battingStats->update(['status' => 'striker']);
            } elseif ($matchBattingStats->count() == 2) {
                $battingStats->update(['status' => 'non-striker']);
            }
        });
    }
}
