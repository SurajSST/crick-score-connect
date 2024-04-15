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
        $user = User::inRandomOrder()->first();
        $matchIds = [1, 2];
        $innings = Innings::factory()->create();

        return [
            'user_id' => $user->id,
            'match_id' => $this->faker->randomElement($matchIds),
            'innings_id' => $innings->id,
            'runs_scored' => $this->faker->numberBetween(0, 200),
            'fours' => $this->faker->numberBetween(0, 50),
            'sixes' => $this->faker->numberBetween(0, 20),
            'strike_rate' => $this->faker->randomFloat(2, 50, 200),
            'balls_faced' => $this->faker->numberBetween(0, 300),
        ];
    }
}
