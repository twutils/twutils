<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\DestroyTweetJob;
use Tests\Feature\TwitterOperations\Shared\DestroyTweetsTest;

class DestroyTweetJobTest extends DestroyTweetsTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = DestroyTweetJob::class;
        $this->apiEndpoint = '/api/destroyTweets';
        $this->twitterEndpoint = 'statuses/destroy';
        $this->tweetsSourcetwitterEndpoint = 'statuses/user_timeline';
        $this->tweetsSourceApiEndpoint = '/api/userTweets';
        $this->tweetsSourceListEndpoint = '/api/tasks/userTweets';
    }
}
