<?php

namespace App\TwUtils\TwitterOperations;

class ManagedDestroyTweetsOperation extends ManagedDestroyLikesOperation
{
    protected $shortName = 'ManagedDestroyTweets';
    protected $tasksQueue = [FetchUserTweetsOperation::class, destroyTweetsOperation::class];
    protected $firstTaskShortName = FetchUserTweetsOperation::class;
    protected $secondTaskShortName = destroyTweetsOperation::class;
}
