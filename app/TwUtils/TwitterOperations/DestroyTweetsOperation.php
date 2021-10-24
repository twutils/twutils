<?php

namespace App\TwUtils\TwitterOperations;

class DestroyTweetsOperation extends DestroyLikesOperation
{
    protected $shortName = 'DestroyTweets';

    protected $scope = 'write';
}
