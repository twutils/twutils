<?php

namespace App\TwUtils\TwitterOperations;

class DestroyTweetsOperation extends DestroyLikesOperation
{
    protected $scope = 'write';
}
