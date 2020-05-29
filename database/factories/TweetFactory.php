<?php

use Faker\Generator as Faker;

$factory->define(App\Tweet::class, function (Faker $faker) {
    return [
        'id_str'           => $faker->randomNumber(),
        'text'             => $faker->sentence,
        'lang'             => 'en',
        'tweet_created_at' => now(),
        'tweep_id_str'     => 1, // TODO: write and use Tweep factory

        'retweet_count'   => $faker->randomNumber(),
        'is_quote_status' => $faker->boolean,
    ];
});
