<?php

namespace App\TwUtils\TwitterOperations;

class ManagedDestroyTweetsOperation extends ManagedDestroyLikesOperation
{
    protected $shortName = 'ManagedDestroyTweets';

    protected $tasksQueue = [FetchUserTweetsOperation::class, DestroyTweetsOperation::class];
}
