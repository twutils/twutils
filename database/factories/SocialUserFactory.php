<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SocialUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SocialUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'        => User::factory(),
            'social_user_id' => 1230,
            'token'          => 7890,
            'token_secret'   => 'any0',
            'description'    => $this->faker->paragraph,
            'nickname'       => 'any0',
            'name'           => 'any0',
            'email'          => 'any0',
            'avatar'         => 'any0',
            'scope'          => ['read'],
        ];
    }
}
