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
    protected $model = TeamPlayer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'team_id' => Team::all()->random()->id,
            'user_id' => User::all()->random()->id,
        ];
    }
}
