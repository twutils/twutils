<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchLikesJob;
use App\Jobs\DislikeTweetJob;
use Tests\Feature\TwitterOperations\Shared\ManagedDestroyTaskTest;

class ManagedDestroyLikesTest extends ManagedDestroyTaskTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = DislikeTweetJob::class;
        $this->tweetsListjobName = FetchLikesJob::class;
        $this->apiEndpoint = '/api/ManagedDestroyLikes';
        $this->taskBaseName = 'manageddestroylikes';
        $this->tweetsSourceTaskBaseName = 'destroylikes';
        $this->twitterEndpoint = 'favorites/destroy';
    }
}
