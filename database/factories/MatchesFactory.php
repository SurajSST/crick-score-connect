<?php

namespace Database\Factories;

use App\Models\Matches;
use App\Models\Team;
use App\Models\User;
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
        $team1 = Team::first();
        $team2 = Team::where('id', '!=', $team1->id)->first();
        $tossWinner = $this->faker->randomElement([$team1, $team2]);

        return [
            'team1_id' => $team1->id,
            'team2_id' => $team2->id,
            'date' => $this->faker->dateTime(),
            'time' => $this->faker->time(),
            'toss_winner_id' => $tossWinner->id,
            'toss_winner_batting_first' => $this->faker->boolean(), // Assuming the decision is random
            'venue' => $this->faker->city,
            'overs' => $this->faker->randomDigit,
            'players_per_team' => $this->faker->randomDigit,
            'isGameFinished' => false, // Default value
            'isGameCanceled' => false, // Default value
            'user_id' => User::inRandomOrder()->first()->id,
            'target' => null, // Default value, assuming no target initially
            'CRR' => null, // Default value, assuming no value initially
            'RRR' => null, // Default value, assuming no value initially
            'extras' => null, // Default value, assuming no extras initially
        ];
    }
}
