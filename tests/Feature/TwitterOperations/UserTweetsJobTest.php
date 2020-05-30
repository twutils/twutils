<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\FetchUserTweetsJob;
use Tests\Feature\TwitterOperations\Shared\TweetsTaskTest;

class UserTweetsJobTest extends TweetsTaskTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->jobName = FetchUserTweetsJob::class;
        $this->apiEndpoint = '/api/userTweets';
        $this->twitterEndpoint = 'statuses/user_timeline';

        $this->initalTwitterParametersKeys = [
            'count',
            'exclude_replies',
            'include_entities',
            'include_rts',
            'screen_name',
            'trim_user',
            'tweet_mode',
            'user_id',
        ];
    }
}
