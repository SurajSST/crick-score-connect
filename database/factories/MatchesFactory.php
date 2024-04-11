<?php

namespace Database\Factories;

use App\Models\Matches;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatchesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Matches::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'team1_id' => Team::all()->random()->id,
            'team2_id' => Team::all()->random()->id,
            'date' => $this->faker->dateTime(),
            'time' => $this->faker->time(),
            'match_result' => $this->faker->randomElement(['Win', 'Loss', 'Draw']),
            'toss_winner_id' => Team::all()->random()->id,
            'venue' => $this->faker->city,
            'overs' => $this->faker->randomDigit,
            'players_per_team' => $this->faker->randomDigit,
        ];
    }
}
