<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchEntitiesLikesJob;
use Tests\Feature\TwitterOperations\Shared\EntitiesTaskTests;

class EntitiesLikesJobTest extends EntitiesTaskTests
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = FetchEntitiesLikesJob::class;
        $this->apiEndpoint = '/api/entitiesLikes';
        $this->twitterEndpoint = 'favorites/list';

        $this->initalTwitterParametersKeys = [
			'count',
			'include_entities',
			'screen_name',
			'tweet_mode',
			'user_id',
        ];
    }
}
