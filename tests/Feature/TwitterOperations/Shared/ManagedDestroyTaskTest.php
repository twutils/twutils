<?php

namespace Tests\Feature\TwitterOperations\Shared;

use App\Models\Task;
use App\Models\Tweet;
use Tests\TwitterClientMock;
use Tests\IntegrationTestCase;

abstract class ManagedDestroyTaskTest extends IntegrationTestCase
{
    public function test_managed_destroy_tweets()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        $response = $this->postJson($this->apiEndpoint, []);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertCount(1, Tweet::all());

        $this->assertCountDispatchedJobs(1, $this->tweetsListjobName);

        $this->assertCountDispatchedJobs(1, $this->jobName);

        $this->assertTaskCount(3, 'completed');

        $this->assertEquals(1, Task::all()->last()->managedBy->id);
    }

    public function test_managed_destroy_tweets_custom_date_range()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweets = $this->generateUniqueTweets(10);

        $this->postJson($this->apiEndpoint, [
            'settings' => [
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date'   => now()->subDays(3)->format('Y-m-d'),
            ],
        ]);

        $this->fireJobsAndBindTwitter([
            [
                'type'           => $this->tweetsListjobName,
                'twitterData'    => $tweets,
            ],
        ]);

        $response = $this->getJson('api/tasks/')
                    ->assertSuccessful()
                    ->json();

        $this->assertEquals('4/4', $response[0]['removedCount']);
        $this->assertEquals('4/4', $response[2]['removedCount']);
        $this->assertEquals(4, $response[2]['extra']['removeScopeCount']);
        $this->assertEquals($this->taskBaseName, $response[0]['baseName']);
        $this->assertEquals($this->tweetsSourceTaskBaseName, $response[2]['componentName']);

        $this->assertCount(4, Tweet::all());

        $this->assertCount(4, TwitterClientMock::getAllCallsData()->where('path', $this->twitterEndpoint));

        $this->assertCountDispatchedJobs(1, $this->tweetsListjobName);

        $this->assertCountDispatchedJobs(4, $this->jobName);

        $this->assertTaskCount(3, 'completed');

        $this->assertEquals(1, Task::all()->last()->managedBy->id);
    }

    public function test_managed_destroy_tweets_broken()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();
        config()->set('twutils.minimum_expected_likes', 1);

        $rateLimit = $this->getStub('rate_limit_response.json');
        $tweets = $this->generateTweets(3);
        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $response = $this->postJson($this->apiEndpoint, []);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([
            [
                'type'        => $this->tweetsListjobName,
                'twitterData' => [$tweets[0]],
            ],
            [
                'type'           => $this->tweetsListjobName,
                'twitterData'    => $twitterNotExistResponse,
                'twitterHeaders' => ['x_rate_limit_remaining' => '0', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')],
            ],
            [
                'type'        => $this->tweetsListjobName,
                'twitterData' => [$tweets[2]],
            ],
        ]);

        $this->assertCount(1, Tweet::all());

        $this->assertCountDispatchedJobs(2, $this->tweetsListjobName);

        $this->assertCountDispatchedJobs(0, $this->jobName);

        $this->assertTaskCount(2);
        $this->assertTaskCount(2, 'broken');

        $this->assertEquals(1, Task::all()->last()->managedBy->id);
    }

    public function test_managed_destroy_tweets_error_on_task_add()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();
        config()->set('twutils.minimum_expected_likes', 1);

        $response = $this->postJson($this->apiEndpoint, ['settings' => [
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
