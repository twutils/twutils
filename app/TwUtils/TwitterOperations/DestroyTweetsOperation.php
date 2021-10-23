<?php

namespace App\TwUtils\TwitterOperations;

class DestroyTweetsOperation extends DestroyLikesOperation
{
    protected $shortName = 'DestroyTweets';

    protected $endpoint = 'statuses/destroy';

    protected $scope = 'write';

    protected $httpMethod = 'post';
}
