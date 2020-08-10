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
        $this->twitterEndpoint = 'favorites/list';

        $this->initalTwitterParametersKeys = [
            'count',
            'include_entities',
            'screen_name',
            'tweet_mode',
            'user_id',
        ];

        $this->exportTaskShortName = 'backup-likes';
    }
}
