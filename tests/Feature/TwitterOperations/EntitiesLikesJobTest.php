<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchEntitiesLikesJob;
use Tests\Feature\TwitterOperations\Shared\EntitiesTaskTests;

class EntitiesLikesJobTest extends EntitiesTaskTests
{
    public function setUp() : void
    {
        parent::setUp();

        $this->jobName = FetchEntitiesLikesJob::class;
        $this->apiEndpoint = '/api/entitiesLikes';
    }
}
