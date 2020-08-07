<?php

namespace App\TwUtils\TwitterOperations;

use App\Jobs\DestroyTweetJob;

class DestroyTweetsOperation extends DestroyLikesOperation
{
    protected $shortName = 'DestroyTweets';
    protected $endpoint = 'statuses/destroy';
    protected $scope = 'write';
    protected $httpMethod = 'post';
    protected $dispatchJobName = DestroyTweetJob::class;
}
