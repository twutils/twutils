<?php

namespace Tests\Feature\TwitterOperations;

use App\Task;
use App\User;
use App\Tweet;
use App\Jobs\DislikeTweetJob;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Bus;

class DislikeTweetJobTest extends IntegrationTestCase
{
    public function test_basic_dislike()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DislikeTweetJob::class);
    }

    public function test_basic_dislike_no_tweets()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([]);

        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertCountDispatchedJobs(0, DislikeTweetJob::class);
        $this->assertTaskCount(2, 'completed');
    }

    public function test_dislike_one_tweet_not_exist()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        // While retrieving likes, the tweet exists
        $tweet = $this->getStub('tweet.json');
        $this->bindTwitterConnector([$tweet]);
        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        // But when destroying, it doesn't exist in the user likes anymore
        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');
        $this->bindTwitterConnector($twitterNotExistResponse);

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        // Ignore the fact that it doesn't exist anymore, and proceed business as usual..
        $this->assertCountDispatchedJobs(1, DislikeTweetJob::class);
        $this->assertTaskCount(2, 'completed');
    }

    public function test_dislike_two_tweets_second_not_exist()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');
        $secondTweet = clone $tweet;
        $secondTweet->id_str = (string) time();

        $this->bindTwitterConnector([$tweet, $secondTweet]);
        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
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

    public function test_dislike_two_tweets_second_unknown_error()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');
        $secondTweet = clone $tweet;
        $secondTweet->id_str = (string) time();

        $this->bindTwitterConnector([$tweet, $secondTweet]);
        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
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

    public function test_dislike_three_tweets_second_unknown_error()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');
        $secondTweet = clone $tweet;
        $secondTweet->id_str = (string) time();

        $thirdTweet = clone $tweet;
        $thirdTweet->id_str = (string) (time() + 1);

        $tweets = [$tweet, $secondTweet, $thirdTweet];

        $this->bindTwitterConnector($tweets);
        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $twitterNotExistResponse->errors[0]->code = 54321;

        $dislikeTweetCount = 0;
        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            if ($this->dispatchedJobs[$i] instanceof DislikeTweetJob) {
                $dislikeTweetCount++;
            }

            if ($dislikeTweetCount == 2) {
                $this->bindTwitterConnector($twitterNotExistResponse);
            } else {
                $this->bindTwitterConnector($tweets);
            }

            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertEquals(1, Task::all()->where('status', 'completed')->count());
        $this->assertEquals(1, Task::all()->where('status', 'broken')->count());
        $this->assertNotNull(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertNull(Task::find(1)->tweets->values()[1]->pivot->removed);
        $this->assertNull(Task::find(1)->tweets[2]->pivot->removed);
    }

    public function test_dislike_three_tweets_second_not_exist()
    {
        $this->logInSocialUserForDestroyLikes();
        $this->withoutJobs();

        $tweet = $this->getStub('tweet.json');
        $secondTweet = clone $tweet;
        $secondTweet->id_str = (string) time();

        $thirdTweet = clone $tweet;
        $thirdTweet->id_str = (string) (time() + 1);

        $tweets = [$tweet, $secondTweet, $thirdTweet];

        $this->bindTwitterConnector($tweets);
        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        $twitterNotExistResponse = $this->getStub('tweet_id_not_exist_response.json');

        $dislikeTweetCount = 0;
        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            if ($this->dispatchedJobs[$i] instanceof DislikeTweetJob) {
                $dislikeTweetCount++;
            }

            if ($dislikeTweetCount == 2) {
                $this->bindTwitterConnector($twitterNotExistResponse);
            } else {
                $this->bindTwitterConnector($tweets);
            }

            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertEquals(2, Task::all()->where('status', 'completed')->count());
        $this->assertNotNull(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertNull(Task::find(1)->tweets->values()[1]->pivot->removed);
        $this->assertNotNull(Task::find(1)->tweets[2]->pivot->removed);
    }

    public function test_basic_unauthorized_dislike()
    {
        $this->logInSocialUserForDestroyLikes();
        Bus::fake();

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);

        $taskId = (int) $response->decodeResponseJson()['data']['task_id'];

        $taskId = $taskId + 1;  // wrong task id!

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);

        $response->assertStatus(401);

        Bus::assertNotDispatched(DislikeTweetJob::class);
    }

    public function test_dislike_tweet_job()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyLikes();

        $tweet = $this->getStub('tweet.json');
        $this->bindTwitterConnector([$tweet, $tweet]);

        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'favorites/list');

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DislikeTweetJob::class);

        for ($i = $indexLastDispatched; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'favorites/destroy');

        $this->assertCountDispatchedJobs(1, DislikeTweetJob::class);
        $removedDate = new \Carbon\Carbon(Task::find(1)->tweets->first()->pivot->removed);
        $this->assertLessThanOrEqual(10, $removedDate->diffInSeconds(now()));
        $this->assertTaskCount(2, 'completed');
    }

    public function test_dislike_many_tweets()
    {
        $this->withoutJobs();

        $this->logInSocialUserForDestroyLikes();

        $tweet = $this->getStub('tweet.json');
        $tweets = array_fill(0, 50, (array) $tweet);

        for ($i = 0; $i < 40; $i++) {
            $tweets[$i]['id_str'] = (string) $i;
        }

        foreach ($tweets as $index => $tweet) {
            $tweets[$index] = (object) $tweet;
        }

        $this->bindTwitterConnector($tweets);

        [$indexLastDispatched, $taskId] = $this->fetchLikes();

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'favorites/list');
        $this->assertTrue(\DB::table('task_tweet')->where('removed', '!=', null)->get()->count() == 0);

        $response = $this->postJson('/api/destroyLikes', ['id' => $taskId]);
        $response->assertStatus(200);

        $this->assertCountDispatchedJobs(1, DislikeTweetJob::class);

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
                $this->assertNotNull($this->dispatchedJobs[$delayIndex + $indexLastDispatched - 4]->delay);
            }
        );

        $this->assertEquals($this->lastTwitterClientData()['endpoint'], 'favorites/destroy');

        $this->assertFalse(\DB::table('task_tweet')->where('removed', '!=', null)->get()->count() == 0);

        $this->assertTaskCount(2, 'completed');
    }

    private function fetchLikes()
    {
        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);

        for ($i = 0; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $response = $this->getJson('/api/tasks/likes');
        $taskId = $response->decodeResponseJson()[0]['id'];

        $indexLastDispatched = count($this->dispatchedJobs);

        return [$indexLastDispatched, $taskId];
    }
}
