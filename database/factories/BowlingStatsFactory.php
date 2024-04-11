<?php

namespace Database\Factories;

use App\Models\BowlingStats;
use App\Models\Innings;
use App\Models\Match;
use App\Models\Matches;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BowlingStatsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BowlingStats::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::all()->random()->id,
            'match_id' => Matches::all()->random()->id,
            'innings_id' => Innings::all()->random()->id,
            'overs_bowled' => $this->faker->randomFloat(2, 0, 50),
            'runs_conceded' => $this->faker->numberBetween(0, 100),
            'wickets_taken' => $this->faker->numberBetween(0, 10),
            'maidens' => $this->faker->numberBetween(0, 5),
            'economy_rate' => $this->faker->randomFloat(2, 0, 20),
        ];
    }
}
