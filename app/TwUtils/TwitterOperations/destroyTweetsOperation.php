<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use Carbon\Carbon;
use App\Jobs\DestroyTweetJob;

class destroyTweetsOperation extends destroyLikesOperation
{
    protected $endpoint = 'statuses/destroy';
    protected $scope = 'write';
    protected $httpMethod = 'post';
    protected $dispatchJobName = DestroyTweetJob::class;
}
