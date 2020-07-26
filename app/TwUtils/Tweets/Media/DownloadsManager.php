<?php

namespace App\TwUtils\Tweets\Media;

use App\Tweet;

class DownloadsManager
{
    protected $tweet;
    protected $taskId;

    public function __construct(Tweet $tweet, $taskId)
    {
        $this->tweet = $tweet;
        $this->taskId = $taskId;
    }
}
