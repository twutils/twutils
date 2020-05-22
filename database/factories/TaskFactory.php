<?php

use App\SocialUser;
use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker, $data = []) {
    $socialUserId = $data['socialuser_id'] ?? factory(SocialUser::class)->create()->id;

    return [
        'socialuser_id' => $socialUserId,
        'type'          => 'type',
        'status'        => 'queued',
        'extra'         => [],
    ];
});
