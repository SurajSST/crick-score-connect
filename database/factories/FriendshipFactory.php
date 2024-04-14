<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Database\Eloquent\Factories\Factory;

class FriendshipFactory extends Factory
{
    protected $model = Friendship::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user1Id = $this->faker->randomElement([1, 2, 3]);

        return [
            'user1_id' => $user1Id,
            'user2_id' => User::where('id', '!=', $user1Id)->inRandomOrder()->first()->id,
            'status' => 'active', // Set the default status to active
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Friendship $friendship) {
            // Ensure each user has at least 10 friends with status active
            $user1 = User::find($friendship->user1_id);
            $this->createAdditionalFriends($user1);
        });
    }

    /**
     * Create additional friends for a user if they have less than 10 active friends.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function createAdditionalFriends(User $user)
    {
        if (in_array($user->id, [1, 2, 3]) && $user->friends()->where('status', 'active')->count() < 10) {
            $friend = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $user->friends->pluck('id'))
                ->inRandomOrder()
                ->first();

            Friendship::create([
                'user1_id' => $user->id,
                'user2_id' => $friend->id,
                'status' => 'active',
            ]);
        }
    }
}
