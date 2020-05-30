<?php

namespace Tests\Feature\TwitterOperations;

use App\Task;
use App\User;
use App\Tweet;
use App\Jobs\DestroyTweetJob;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Bus;

class DestroyTweetJobTest extends IntegrationTestCase
{
    public function test_basic_destroy()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DestroyTweetJob::class);
    }

    public function test_basic_destroy_no_tweets()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $this->bindTwitterConnector([]);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([], $indexLastDispatched);

        // Ignore the fact that it doesn't exist anymore, and proceed business as usual..
        $this->assertCountDispatchedJobs(1, DestroyTweetJob::class);
        $this->assertTaskCount(2, 'completed');
    }

    public function test_destroy_two_tweets_second_not_exist()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');
        $secondTweet = clone $tweet;
        $secondTweet->id_str = (string) time();

        $this->bindTwitterConnector([$tweet, $secondTweet]);
        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            if ($i != $indexLastDispatched) {
                $this->bindTwitterConnector($twitterNotExistResponse);
            }
            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertTaskCount(2, 'completed');
        $this->assertNotNull(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertNull(Task::find(1)->tweets->last()->pivot->removed);
    }

    public function test_destroy_two_tweets_second_unknown_error()
    {
        $this->logInSocialUserForDestroyTweets();
        $this->withoutJobs();

        $tweets = $this->bindMultipleTweets(2);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $twitterNotExistResponse->errors[0]->code = 54321;

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            if ($i != $indexLastDispatched) {
                $this->bindTwitterConnector($twitterNotExistResponse);
            }
            $this->dispatchedJobs[$i]->handle();
        }

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

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $twitterNotExistResponse->errors[0]->code = 54321;

        $this->fireJobsAndBindTwitter([
            [
                'type'        => DestroyTweetJob::class,
                'twitterData' => $tweets[0],
            ],
            [
                'type'        => DestroyTweetJob::class,
                'twitterData' => $twitterNotExistResponse,
            ],
            [
                'type'        => DestroyTweetJob::class,
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

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $this->fireJobsAndBindTwitter([
            [
                'type'        => DestroyTweetJob::class,
                'twitterData' => $tweets[0],
            ],
            [
                'type'        => DestroyTweetJob::class,
                'twitterData' => $twitterNotExistResponse,
            ],
            [
                'type'        => DestroyTweetJob::class,
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

        $response = $this->getJson('/api/userTweets');
        $response->assertStatus(200);

        $taskId = (int) $response->decodeResponseJson()['data']['task_id'];

        $taskId = $taskId + 1;  // wrong task id!

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);

        $response->assertStatus(401);

        Bus::assertNotDispatched(DestroyTweetJob::class);
    }

    public function test_destroy_tweet_job()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $tweets = $this->generateTweets(2);
        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DestroyTweetJob::class);

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/destroy');

        $this->assertCountDispatchedJobs(2, DestroyTweetJob::class);
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

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DestroyTweetJob::class);

        Task::first()->delete();
        Tweet::where('task_id', $taskId)->delete();

        $this->fireJobsAndBindTwitter([], $indexLastDispatched);

        $this->assertCountDispatchedJobs(5, DestroyTweetJob::class);
        $this->assertTaskCount(1);
    }

    public function test_destroy_many_tweets()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');
        $this->assertTrue(Task::find(1)->tweets->where('pivot.removed', '!=', null)->count() == 0);

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DestroyTweetJob::class);

        $delaysIndexes = collect([10, 20, 30]);

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();

            if ($delaysIndexes->contains($i)) {
                $this->bindTwitterConnector([], ['x_rate_limit_remaining' => '1', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')]);
            } else {
                $this->bindTwitterConnector([]);
            }
        }
        $delaysIndexes->each(
            function ($delayIndex) use ($indexLastDispatched) {
                $this->assertNotNull($this->dispatchedJobs[$delayIndex + $indexLastDispatched - 1]->delay);
            }
        );

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/destroy');

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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['start_date' => now()->subHour()->format('Y-m-d')]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/destroy');

        $this->assertCountDispatchedJobs(2, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['start_date' => now()->subWeek()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/destroy');

        $this->assertCountDispatchedJobs(3, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => [
            'retweets'   => false,
            'tweets'     => false,
            'replies'    => false,
            'start_date' => '2018-05-01',
            'end_date'   => '2018-01-01',
        ]]);

        $response->assertStatus(422);

        $this->fireJobsAndBindTwitter();

        $this->assertNotEquals($this->lastTwitterClientData()['endpoint'], 'statuses/destroy');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
        $this->assertTaskCount(1, 'completed');
    }

    public function test_destroy_many_tweets_with_custom_start_date_remove_nothing()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyTweets();

        $this->bindMultipleTweets(40);

        [$indexLastDispatched, $taskId] = $this->fetchTweets();

        $this->bindTwitterConnector([]);
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['start_date' => now()->format('Y-m-d')]]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['start_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Start Date', implode($response->decodeResponseJson()['errors']));
        $this->assertFalse(isset($response->json()['end_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['end_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('End Date', implode($response->decodeResponseJson()['errors']));
        $this->assertFalse(isset($response->json()['start_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('End Date', implode($response->decodeResponseJson()['errors']));
        $this->assertFalse(isset($response->json()['start_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['end_date' => now()->format('Y-m-d'), 'start_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Start Date', implode($response->decodeResponseJson()['errors']));
        $this->assertFalse(isset($response->json()['end_date']));

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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
        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId, 'settings' => ['end_date' => now()->format('Y-D-M'), 'start_date' => now()->format('H:i:s')]]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Start Date', implode($response->decodeResponseJson()['errors']));
        $this->assertStringContainsString('End Date', implode($response->decodeResponseJson()['errors']));
        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
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

        $response = $this->postJson('/api/destroyTweets', ['id' => $taskId]);

        $response->assertStatus(401);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'statuses/user_timeline');

        $this->assertCountDispatchedJobs(0, DestroyTweetJob::class);
        $this->assertEquals(40, Task::find(1)->tweets->where('pivot.removed', '=', null)->count());

        $this->assertTaskCount(1, 'completed');
    }

    protected function fetchTweets()
    {
        $response = $this->getJson('/api/userTweets');
        $response->assertStatus(200);

        for ($i = 0; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $response = $this->getJson('/api/tasks/userTweets');
        $taskId = $response->decodeResponseJson()[0]['id'];

        $indexLastDispatched = count($this->dispatchedJobs);

        return [$indexLastDispatched, $taskId];
    }
}
