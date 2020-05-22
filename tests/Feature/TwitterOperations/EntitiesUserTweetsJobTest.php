<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchEntitiesUserTweetsJob;
use Tests\Feature\TwitterOperations\Shared\EntitiesTaskTests;

class EntitiesUserTweetsJobTest extends EntitiesTaskTests
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = FetchEntitiesUserTweetsJob::class;
        $this->apiEndpoint = '/api/entitiesUserTweets';
    }
}
