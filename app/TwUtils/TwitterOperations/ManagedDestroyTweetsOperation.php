<?php

namespace App\TwUtils\TwitterOperations;

class ManagedDestroyTweetsOperation extends ManagedDestroyLikesOperation
{
    protected $tasksQueue = [FetchUserTweetsOperation::class, destroyTweetsOperation::class];
    protected $firstTaskShortName = 'UserTweets';
    protected $secondTaskShortName = 'DestroyTweets';
}
