<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchLikesJob;
use Tests\Feature\TwitterOperations\Shared\TweetsTaskTest;

class LikesJobTest extends TweetsTaskTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = FetchLikesJob::class;
        $this->apiEndpoint = '/api/likes';
    }
}
