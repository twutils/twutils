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
        $this->twitterEndpoint = 'statuses/user_timeline';

        $this->initalTwitterParametersKeys = [
			'count',
			'include_entities',
			'screen_name',
			'tweet_mode',
			'user_id',
        ];
    }
}
