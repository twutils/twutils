<?php

namespace App\TwUtils\TwitterOperations;

use App\Jobs\CompleteManagedDestroyLikesJob;
use App\SocialUser;
use App\Task;
use App\TwUtils\SnapshotsManager;
use App\TwUtils\TasksAdder;
use App\TwUtils\TwitterOperations\destroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;

class ManagedDestroyTweetsOperation extends ManagedDestroyLikesOperation
{
    protected $tasksQueue = [FetchUserTweetsOperation::class, destroyTweetsOperation::class];
    protected $firstTaskShortName = 'UserTweets';
    protected $secondTaskShortName = 'DestroyTweets';
}
