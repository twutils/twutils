<?php

namespace Tests\Feature\TwitterOperations\Shared;

use App\Models\Task;
use App\Models\Tweet;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Bus;

/*
 * A Generic abstract tests for all tasks that deletes tweets.
 * Mainly, it's "Dislike Liked Tweets", and "Remove User Tweets".
 */
abstract class DestroyTweetsTest extends IntegrationTestCase
{
    public function test_basic_destroy()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, $this->jobName);
    }

    public function test_basic_destroy_no_tweets()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $this->bindTwitterConnector([]);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_one_tweet_not_exist()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        // While retrieving user tweets, the tweet exists
        $tweet = $this->getStub('tweet.json');
        $this->bindTwitterConnector([$tweet]);
        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        // But when destroying, it doesn't exist in the user user tweets anymore
        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');
        $this->bindTwitterConnector($twitterNotExistResponse);

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([], $indexLastDispatched);

        // Ignore the fact that it doesn't exist anymore, and proceed business as usual..
        $this->assertCountDispatchedJobs(1, $this->jobName);
        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_two_tweets_second_not_exist()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweets = $this->generateTweets(2);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $this->fireJobsAndBindTwitter([
            [
                'type'        => $this->jobName,
                'twitterData' => $tweets[0],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => $twitterNotExistResponse,
            ],
        ], $indexLastDispatched);

        $this->assertTaskCount(2, 'completed');
        $this->assertNotNull(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertNull(Task::find(1)->tweets->last()->pivot->removed);
    }

    public function test_destroy_two_tweets_second_unknown_error()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweets = $this->generateTweets(2);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $twitterNotExistResponse->errors[0]->code = 54321;

        $this->fireJobsAndBindTwitter([
            [
                'type'        => $this->jobName,
                'twitterData' => $tweets[0],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => $twitterNotExistResponse,
            ],
        ], $indexLastDispatched);

        $this->assertEquals(1, Task::all()->where('status', 'completed')->count());
        $this->assertEquals(1, Task::all()->where('status', 'broken')->count());
        $this->assertNotNull(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertNull(Task::find(1)->tweets->last()->pivot->removed);
    }

    public function test_destroy_three_tweets_second_unknown_error()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweets = $this->generateTweets(3);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $twitterNotExistResponse->errors[0]->code = 54321;

        $this->fireJobsAndBindTwitter([
            [
                'type'        => $this->jobName,
                'twitterData' => $tweets[0],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => $twitterNotExistResponse,
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => $tweets[2],
            ],
        ], $indexLastDispatched);

        $this->assertEquals(1, Task::all()->where('status', 'completed')->count());
        $this->assertEquals(1, Task::all()->where('status', 'broken')->count());
        $this->assertEquals(3, Task::find(1)->likes_count);
        $this->assertNotNull(Task::find(1)->tweets[0]->pivot->removed);
        $this->assertNull(Task::find(1)->tweets[1]->pivot->removed);
        $this->assertNull(Task::find(1)->tweets[2]->pivot->removed);
    }

    public function test_destroy_three_tweets_second_not_exist()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweets = $this->generateTweets(3);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $this->fireJobsAndBindTwitter([
            [
                'type'        => $this->jobName,
                'twitterData' => $tweets[0],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => $twitterNotExistResponse,
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => $tweets[2],
            ],
        ], $indexLastDispatched);

        $this->assertEquals(2, Task::all()->where('status', 'completed')->count());
        $this->assertNotNull(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertNull(Task::find(1)->tweets[1]->pivot->removed);
        $this->assertNotNull(Task::find(1)->tweets[2]->pivot->removed);
    }

    public function test_basic_unauthorized_destroy()
    {
        $this->logInSocialUserForDestroyTweets();
        Bus::fake();

        $this->bindMultipleTweets(2);

        $response = $this->getJson($this->tweetsSourceApiEndpoint);
        $response->assertStatus(200);

        $taskId = (int) $response->json()['data']['task_id'];

        $taskId = $taskId + 1;  // wrong task id!

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);

        $response->assertStatus(401);

        Bus::assertNotDispatched($this->jobName);
    }

    public function test_destroy_tweet_job()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $tweets = $this->generateTweets(2);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, $this->jobName);

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->twitterEndpoint);

        $this->assertCountDispatchedJobs(2, $this->jobName);
        $removedDate = new \Carbon\Carbon(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertLessThanOrEqual(10, $removedDate->diffInSeconds(now()));
        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_tweets_while_tweets_source_is_removed()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $tweets = $this->generateTweets(5);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, $this->jobName);

        Task::first()->delete();
        Tweet::where('task_id', $taskId)->delete();

        $this->fireJobsAndBindTwitter([], $indexLastDispatched);

        $this->assertCountDispatchedJobs(5, $this->jobName);
        $this->assertTaskCount(1);
    }

    public function test_destroy_many_tweets()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, $this->jobName);

        $this->fireJobsAndBindTwitter([
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
            [
                'type'           => $this->jobName,
                'twitterHeaders' => ['x_rate_limit_remaining' => '1', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')],
                'twitterData'    => [],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
            [
                'type'           => $this->jobName,
                'twitterHeaders' => ['x_rate_limit_remaining' => '1', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')],
                'twitterData'    => [],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
            [
                'type'        => $this->jobName,
                'twitterData' => [],
            ],
        ], $indexLastDispatched);

        $this->assertCount(2, collect($this->dispatchedJobs)->filter(function ($job) {
            return $job->delay;
        }));
        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->twitterEndpoint);

        $this->assertFalse(Task::find(1)->tweets->where('pivot.removed', '!=', null)->count() == 0);

        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_many_tweets_with_custom_start_date_remove_only_two_tweets()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $tweets = $this->generateTweets(40);

        $tweets[5]->created_at = now()->format('M j H:i:s P Y');
        $tweets[6]->created_at = now()->format('M j H:i:s P Y');

        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['start_date' => now()->subHour()->format('Y-m-d')]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->twitterEndpoint);

        $this->assertCountDispatchedJobs(2, $this->jobName);
        $this->assertEquals(2, Task::find(1)->tweets->where('pivot.removed', '!=', null)->count());

        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_many_tweets_with_custom_start_and_end_date_remove_only_three_tweets()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $tweets = $this->generateTweets(40);

        $tweets[5]->created_at = now()->subDays(1)->format('M j H:i:s P Y');
        $tweets[9]->created_at = now()->subDays(2)->format('M j H:i:s P Y');
        $tweets[15]->created_at = now()->subDays(3)->format('M j H:i:s P Y');

        $tweets[10]->created_at = now()->addDays(1)->format('M j H:i:s P Y');
        $tweets[18]->created_at = now()->addDays(2)->format('M j H:i:s P Y');

        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['start_date' => now()->subWeek()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->twitterEndpoint);

        $this->assertCountDispatchedJobs(3, $this->jobName);
        $this->assertEquals(3, Task::find(1)->tweets->where('pivot.removed', '!=', null)->count());

        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_many_tweets_end_date_less_than_start_date()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $tweets = $this->generateTweets(10);

        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => [
            'retweets'   => false,
            'tweets'     => false,
            'replies'    => false,
            'start_date' => '2018-05-01',
            'end_date'   => '2018-01-01',
        ]]);

        $response->assertStatus(422);

        $this->fireJobsAndBindTwitter();

        $this->assertNotEquals($this->lastTwitterClientData()['endpoint'], $this->twitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_many_tweets_with_custom_start_date_remove_nothing()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['start_date' => now()->format('Y-m-d')]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_tweets_accepts_valid_start_date_format_only()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['start_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Start Date', implode($response->json()['errors']));
        $this->assertFalse(isset($response->json()['end_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_tweets_accepts_valid_end_date_format_only()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['end_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('End Date', implode($response->json()['errors']));
        $this->assertFalse(isset($response->json()['start_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_tweets_reject_if_valid_start_date_but_invalid_end_date()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('End Date', implode($response->json()['errors']));
        $this->assertFalse(isset($response->json()['start_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_tweets_reject_if_valid_end_date_but_invalid_start_date()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['end_date' => now()->format('Y-m-d'), 'start_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Start Date', implode($response->json()['errors']));
        $this->assertFalse(isset($response->json()['end_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_tweets_reject_if_invalid_end_date_and_invalid_start_date()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId, 'settings' => ['end_date' => now()->format('Y-D-M'), 'start_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Start Date', implode($response->json()['errors']));
        $this->assertStringContainsString('End Date', implode($response->json()['errors']));
        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_tweets_reject_if_invalid_task_id()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);

        $this->logInSocialUserForDestroyTweets();

        $response = $this->postJson($this->apiEndpoint, ['id' => $taskId]);

        $response->assertStatus(401);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], $this->tweetsSourcetwitterEndpoint);

        $this->assertCountDispatchedJobs(0, $this->jobName);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    protected function fetchTweets()
    {
        $response = $this->getJson($this->tweetsSourceApiEndpoint);
        $response->assertStatus(200);

        for ($i = 0; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $response = $this->getJson($this->tweetsSourceListEndpoint);
        $taskId = $response->json()[0]['id'];

        $indexLastDispatched = count($this->dispatchedJobs);

        return [$indexLastDispatched, $taskId];
    }
}
