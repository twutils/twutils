<?php

namespace App\TwUtils\TwitterOperations;

class ManagedDestroyTweetsOperation extends ManagedDestroyLikesOperation
{
    protected $tasksQueue = [FetchUserTweetsOperation::class, DestroyTweetsOperation::class];
}
