<?php

namespace Database\Factories;

use App\Models\Tweet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TweetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tweet::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_str'           => $this->faker->randomNumber(),
            'text'             => $this->faker->sentence,
            'lang'             => 'en',
            'tweet_created_at' => now(),
            'tweep_id_str'     => 1, // TODO: write and use Tweep factory

            'retweet_count'   => $this->faker->randomNumber(),
            'is_quote_status' => $this->faker->boolean,
        ];
    }
}
