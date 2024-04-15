<?php

namespace Database\Factories;

use App\Models\TeamPlayer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamPlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Teamplayer::class;

    public function definition()
    {
        // Get the teams with IDs 1 and 2
        $team1 = Team::find(1);
        $team2 = Team::find(2);

        // Get a random user
        $user = User::inRandomOrder()->first();

        // Define the team IDs
        $teamId = $this->faker->randomElement([$team1->id, $team2->id]);

        return [
            'team_id' => $teamId,
            'user_id' => $user->id,
        ];
    }


}
