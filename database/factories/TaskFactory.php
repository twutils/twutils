<?php

use App\Task;
use App\SocialUser;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker, $data = []) {
    $socialUserId = $data['socialuser_id'] ?? factory(SocialUser::class)->create()->id;

    return [
        'socialuser_id' => $socialUserId,
        'type'          => collect([FetchLikesOperation::class, FetchUserTweetsOperation::class])->random(),
        'status'        => 'queued',
        'extra'         => ['settings' => []],
    ];
});
