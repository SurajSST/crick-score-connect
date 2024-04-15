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
        $user = User::inRandomOrder()->first();
        $matchIds = [1, 2];
        $innings = Innings::factory()->create();

        return [
            'user_id' => $user->id,
            'match_id' => $this->faker->randomElement($matchIds),
            'innings_id' => $innings->id,
            'overs_bowled' => $this->faker->randomFloat(2, 0, 50),
            'runs_conceded' => $this->faker->numberBetween(0, 100),
            'wickets_taken' => $this->faker->numberBetween(0, 10),
            'maidens' => $this->faker->numberBetween(0, 5),
            'economy_rate' => $this->faker->randomFloat(2, 0, 20),
            'is_bowling' => false, // By default, the player is not bowling
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (BowlingStats $bowlingStats) {
            // Update match relationship to include this bowling stat
            $match = $bowlingStats->match;
            $match->bowlingStats()->save($bowlingStats);
        });
    }
}
