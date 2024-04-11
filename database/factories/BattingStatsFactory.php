<?php

namespace Database\Factories;

use App\Models\BattingStats;
use App\Models\Innings;
use App\Models\Match;
use App\Models\Matches;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BattingStatsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BattingStats::class;

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
            'runs_scored' => $this->faker->numberBetween(0, 200),
            'fours' => $this->faker->numberBetween(0, 50),
            'sixes' => $this->faker->numberBetween(0, 20),
            'strike_rate' => $this->faker->randomFloat(2, 50, 200),
            'balls_faced' => $this->faker->numberBetween(0, 300),
        ];
    }
}
