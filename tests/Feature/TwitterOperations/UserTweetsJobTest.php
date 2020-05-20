<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchUserTweetsJob;
use Tests\Feature\TwitterOperations\Shared\TweetsTaskTest;

class UserTweetsJobTest extends TweetsTaskTest
{
    public function setUp() : void
    {
        parent::setUp();

        $this->jobName = FetchUserTweetsJob::class;
        $this->apiEndpoint = '/api/userTweets';
    }
}
