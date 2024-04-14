<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Matches;
use App\Models\Innings;
use App\Models\BowlingStats;
use Illuminate\Database\Eloquent\Factories\Factory;

class BowlingStatsFactory extends Factory
{
    protected $model = BowlingStats::class;

    public function definition()
    {
        $user = User::all()->random();
        $match = Matches::all()->random();
        $innings = Innings::all()->random();

        return [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'innings_id' => $innings->id,
            'overs_bowled' => $this->faker->randomFloat(2, 0, 50),
            'runs_conceded' => $this->faker->numberBetween(0, 100),
            'wickets_taken' => $this->faker->numberBetween(0, 10),
            'maidens' => $this->faker->numberBetween(0, 5),
            'economy_rate' => $this->faker->randomFloat(2, 0, 20),
            'status' => 'not_bowling', // By default, the player is not bowling
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (BowlingStats $bowlingStats) {
            // Eager load the match relationship
            $bowlingStats->load('match');

            // Check if the match exists and has bowling stats
            if ($bowlingStats->match && $bowlingStats->match->bowlingStats->count() == 1) {
                $bowlingStats->update(['status' => 'bowling']);
            }
        });
    }
}
