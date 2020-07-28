<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\DislikeTweetJob;
use Tests\Feature\TwitterOperations\Shared\DestroyTweetsTest;

class DislikeTweetJobTest extends DestroyTweetsTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = DislikeTweetJob::class;
        $this->apiEndpoint = '/api/destroyLikes';
        $this->twitterEndpoint = 'favorites/destroy';
        $this->tweetsSourcetwitterEndpoint = 'favorites/list';
        $this->tweetsSourceApiEndpoint = '/api/likes';
        $this->tweetsSourceListEndpoint = '/api/tasks/likes';
    }
}
