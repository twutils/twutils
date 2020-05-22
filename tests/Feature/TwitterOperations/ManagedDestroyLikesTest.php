<?php

namespace Tests\Feature\TwitterOperations;

use App\Jobs\DislikeTweetJob;
use App\Jobs\FetchLikesJob;
use App\Task;
use App\Tweet;
use Config;
use Tests\IntegrationTestCase;

class ManagedDestroyLikesTest extends IntegrationTestCase
{
    public function test_managed_destroy_likes()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        $response = $this->postJson('/api/ManagedDestroyLikes', []);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertCount(1, Tweet::all());

        $this->assertCountDispatchedJobs(1, FetchLikesJob::class);

        $this->assertCountDispatchedJobs(1, DislikeTweetJob::class);

        $this->assertTaskCount(3, 'completed');

        $this->assertEquals(1, Task::all()->last()->managedBy->id);
    }

    public function test_managed_destroy_likes_broken()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();
        config()->set('twutils.minimum_expected_likes', 1);

        $rateLimit = $this->getStub('rate_limit_response.json');
        $tweets = $this->generateTweets(3);
        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $response = $this->postJson('/api/ManagedDestroyLikes', []);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([
            [
                'type'        => FetchLikesJob::class,
                'twitterData' => [$tweets[0]],
            ],
            [
                'type'           => FetchLikesJob::class,
                'twitterData'    => $twitterNotExistResponse,
                'twitterHeaders' => ['x_rate_limit_remaining' => '0', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')],
            ],
            [
                'type'        => FetchLikesJob::class,
                'twitterData' => [$tweets[2]],
            ],
        ]);

        $this->assertCount(1, Tweet::all());

        $this->assertCountDispatchedJobs(2, FetchLikesJob::class);

        $this->assertCountDispatchedJobs(0, DislikeTweetJob::class);

        $this->assertTaskCount(2);
        $this->assertTaskCount(2, 'broken');

        $this->assertEquals(1, Task::all()->last()->managedBy->id);
    }

    public function test_managed_destroy_likes_error_on_task_add()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();
        config()->set('twutils.minimum_expected_likes', 1);

        $response = $this->postJson('/api/ManagedDestroyLikes', ['settings' => [
            'start_date' => '2013-09-23',
            'end_date'   => '2020-09-23',
            'id'         => 3,
            'replies'    => true,
            'retweets'   => true,
            'tweets'     => true,
        ]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();
        $this->assertTaskCount(3);
    }
}
