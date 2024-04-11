<?php

namespace Database\Factories;

use App\Models\Innings;
use App\Models\Matches;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class InningsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Innings::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'match_id' => Matches::all()->random()->id,
            'batting_team_id' => Team::all()->random()->id,
            'bowling_team_id' => Team::all()->random()->id,
            'innings_number' => $this->faker->randomElement(['1st innings', '2nd innings']),
        ];
    }
}
