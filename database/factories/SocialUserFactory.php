<?php

use App\SocialUser;
use App\User;
use Faker\Generator as Faker;

$factory->define(SocialUser::class, function (Faker $faker, array $data = []) {
    $userId = $data['user_id'] ?? factory(User::class)->create()->id;

    return [
      'user_id' => $userId,
      'social_user_id' => 1230,
      'token' => 7890,
      'token_secret' => 'any0',
      'description' => $faker->paragraph,
      'nickname' => 'any0',
      'name' => 'any0',
      'email' => 'any0',
      'avatar' => 'any0',
      'scope' => ['read'],
    ];
});
