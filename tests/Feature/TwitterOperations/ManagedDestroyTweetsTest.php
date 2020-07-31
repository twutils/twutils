<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\DestroyTweetJob;
use App\Jobs\FetchUserTweetsJob;
use Tests\Feature\TwitterOperations\Shared\ManagedDestroyTaskTest;

class ManagedDestroyTweetsTest extends ManagedDestroyTaskTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = DestroyTweetJob::class;
        $this->tweetsListjobName = FetchUserTweetsJob::class;
        $this->apiEndpoint = '/api/ManagedDestroyTweets';
        $this->twitterEndpoint = 'statuses/destroy';
        $this->taskBaseName = 'manageddestroytweets';
        $this->tweetsSourceTaskBaseName = 'destroytweets';
    }
}
