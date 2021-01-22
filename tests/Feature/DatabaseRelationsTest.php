<?php

namespace Tests\Feature;

use App\Task;
use App\Tweep;
use App\Tweet;
use App\Follower;
use App\Following;
use App\Jobs\FetchLikesJob;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

class DatabaseRelationsTest extends IntegrationTestCase
{
    public function test_eager_loaded_tweet_tweep_relation()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector(array_fill(0, 10, $tweet));

        $this->getJson('/api/likes')
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        DB::connection()->enableQueryLog();
        $response = $this->getJson('/api/tasks/likes');
        $queries = DB::getQueryLog();

        $this->assertLessThanOrEqual(3, count($queries));
    }

    public function test_basic_task_to_likes_relation()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->bindTwitterConnector($this->generateUniqueTweetsAndTweeps(3, 2));

        $this->getJson('/api/likes')
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertEquals(3, Task::find(1)->likes_count);
    }

    public function test_fetch_likes_operation_respects_pivot_attributes()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweets(3);
        $tweets[0]->retweeted = false;
        $tweets[0]->favorited = true;

        $tweets[1]->retweeted = true;
        $tweets[1]->favorited = false;

        $tweets[2]->retweeted = true;
        $tweets[2]->favorited = true;

        $this->bindTwitterConnector($tweets);

        $this->getJson('/api/likes')
        ->assertStatus(200);

        DB::connection()->enableQueryLog();

        $this->fireJobsAndBindTwitter([]);

        $queries = DB::getQueryLog();

        $this->assertEquals(Task::first()->tweets[0]->pivot->retweeted, 0);
        $this->assertEquals(Task::first()->tweets[0]->pivot->favorited, 1);

        $this->assertEquals(Task::first()->tweets[1]->pivot->retweeted, 1);
        $this->assertEquals(Task::first()->tweets[1]->pivot->favorited, 0);

        $this->assertEquals(Task::first()->tweets[2]->pivot->retweeted, 1);
        $this->assertEquals(Task::first()->tweets[2]->pivot->favorited, 1);

        $this->assertLessThanOrEqual(55, count($queries), 'Too much queries for completing a single task');
    }

    public function test_followings_and_followers_table_will_have_the_latest_tweep_id()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweetsAndTweeps(10, 8);
        $this->bindTwitterConnector($tweets);

        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $lastJobIndex = count($this->dispatchedJobs);

        $followingResponse = $this->fetchFollowingResponse();
        $followingResponse->users = collect($tweets)->pluck('user')->toArray();

        $this->getJson('/api/following');

        $this->getJson('/api/followers');

        $this->bindTwitterConnector($followingResponse);

        $this->fireJobsAndBindTwitter([], $lastJobIndex);

        $followers = Tweep::whereIn('id_str', Follower::all()->pluck('tweep_id_str')->sort())
                            ->get()
                            ->pluck('id');

        $followings = Tweep::whereIn('id_str', Following::all()->pluck('tweep_id_str')->sort())
                            ->get()
                            ->pluck('id');

        // Assert Followers and Followings not empty string like: ',,,,,,,'
        $this->assertNotEquals(Follower::count(), strlen($followers->implode(',')) + 1);
        $this->assertNotEquals(Following::count(), strlen($followings->implode(',')) + 1);

        $lastJobIndex = count($this->dispatchedJobs);

        $tweets = $this->generateUniqueTweetsAndTweeps(10, 8);
        $this->bindTwitterConnector($tweets);

        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([], $lastJobIndex);

        $this->assertNotEquals(
            $followers->implode(','),
            Tweep::whereIn(
                'id_str',
                Follower::all()->pluck('tweep_id_str')
            )
            ->pluck('id')
            ->sort()
            ->implode(',')
        );

        $this->assertNotEquals(
            $followings->implode(','),
            Tweep::whereIn(
                'id_str',
                Following::all()->pluck('tweep_id_str')
            )
            ->pluck('id')
            ->sort()
            ->implode(',')
        );

        $this->assertEquals(4, Task::all()->count());
        $this->assertEquals('completed', Task::find(1)->status);
        $this->assertEquals(8, Tweep::all()->count());
        $this->assertNotNull($this->getJson('/api/tasks/2/data')->decodeResponseJson()['data'][0]['tweep']);
        $this->assertNotNull($this->getJson('/api/tasks/3/data')->decodeResponseJson()['data'][0]['tweep']);
    }

    public function test_two_workers_share_same_tweep_whose_data_will_be_updated_during_following_task()
    {
        // In total, we have three tasks
        // 1. a Tweep, let's say his handle is 'MohannadNaj', was inserted in
        // the first 'fetch likes' task.
        // 2. a 'fetch followings' task was dispatched after. This task
        // includes the same tweep, 'MohannadNaj'.
        // 3. at the same time the second task was dispatched, the third
        // task 'fetch likes' was dispatched and finished, it includes
        // the same tweep: 'MohannadNaj', now his data will be updated
        // and the tweep will be in a different id.

        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweetsAndTweeps(10, 8);
        $this->bindTwitterConnector($tweets);

        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $lastJobIndex = count($this->dispatchedJobs);

        $followingResponse = $this->fetchFollowingResponse();
        $followingResponse->users = collect($tweets)->pluck('user')->toArray();

        $this->getJson('/api/following');

        $this->bindTwitterConnector($followingResponse);

        $tweepsIds = (Tweep::all()->pluck('id_str')->implode(','));
        $hookExecuted = false;
        app()->bind('BeforeFollowingInsertHook', function () use ($tweets, $followingResponse, &$hookExecuted) {
            $this->bindTwitterConnector($tweets);

            $lastJobIndex = count($this->dispatchedJobs);

            $response = $this->getJson('/api/likes');
            $response->assertStatus(200);

            for ($i = $lastJobIndex; $i < count($this->dispatchedJobs); $i++) {
                $this->dispatchedJobs[$i]->handle();
            }

            $this->bindTwitterConnector($followingResponse);
            $hookExecuted = true;
        });

        $this->fireJobsAndBindTwitter([
            [
                'type' => FetchLikesJob::class,
                'skip' => true,
            ],
        ], $lastJobIndex);

        $this->assertTrue($hookExecuted);

        $firstLikesTaskTweepsIds = Task::find(1)->likes->pluck('tweep_id')->sort()->implode(',');
        $firstLikesTaskTweepsIdStrs = Task::find(1)->likes->map->tweep->map->id_str->unique()->sort()->implode(',');
        $followingsTaskTweeps = Task::find(2)->followings->pluck('tweep_id_str')->sort()->implode(',');
        $secondLikesTaskTweepsIds = Task::find(3)->likes->pluck('tweep_id')->sort()->implode(',');
        $secondLikesTaskTweepsIdStrs = Task::find(3)->likes->map->tweep->map->id_str->unique()->sort()->implode(',');

        $followingsTaskResponse = $this->getJson('/api/tasks/2/data')->decodeResponseJson();

        $this->assertEquals(8, Tweep::all()->count());
        $this->assertEquals(8, Task::find(2)->followings->count());
        $this->assertNotNull(1, Tweet::find(1)->tweep);

        $this->assertNotNull($followingsTaskResponse['data'][0]['tweep']);

        $this->assertEquals($firstLikesTaskTweepsIds, $secondLikesTaskTweepsIds);

        $this->assertEquals($firstLikesTaskTweepsIdStrs, $secondLikesTaskTweepsIdStrs);
        $this->assertEquals($firstLikesTaskTweepsIdStrs, $followingsTaskTweeps);

        $this->assertNotNull(Following::find(1)->tweep);
    }

    public function test_two_workers_share_same_tweep_whose_data_will_be_updated_during_followers_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweetsAndTweeps(10, 8);
        $this->bindTwitterConnector($tweets);

        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $lastJobIndex = count($this->dispatchedJobs);

        $followerResponse = $this->fetchFollowingResponse();
        $followerResponse->users = collect($tweets)->pluck('user')->toArray();

        $this->getJson('/api/followers');

        $this->bindTwitterConnector($followerResponse);

        $tweepsIds = (Tweep::all()->pluck('id')->implode(','));
        app()->bind('BeforeFollowersInsertHook', function () use ($tweets, $followerResponse) {
            $this->bindTwitterConnector($tweets);

            $lastJobIndex = count($this->dispatchedJobs);

            $response = $this->getJson('/api/likes');
            $response->assertStatus(200);

            for ($i = $lastJobIndex; $i < count($this->dispatchedJobs); $i++) {
                $this->dispatchedJobs[$i]->handle();
            }
            $this->bindTwitterConnector($followerResponse);
        });

        $this->fireJobsAndBindTwitter([
            [
                'type' => FetchLikesJob::class,
                'skip' => true,
            ],
        ], $lastJobIndex);

        $firstLikesTaskTweepsIds = Task::find(1)->likes->pluck('tweep.id')->sort()->implode(',');
        $firstLikesTaskTweepsIdStrs = Task::find(1)->likes->map->tweep->map->id_str->unique()->sort()->implode(',');
        $followersTaskTweeps = Task::find(2)->followers->pluck('tweep_id_str')->sort()->implode(',');
        $secondLikesTaskTweepsIds = Task::find(3)->likes->pluck('tweep.id')->sort()->implode(',');
        $secondLikesTaskTweepsIdStrs = Task::find(3)->likes->map->tweep->map->id_str->unique()->sort()->implode(',');

        $followersTaskResponse = $this->getJson('/api/tasks/2/data')->decodeResponseJson();

        $this->assertEquals(8, Tweep::all()->count());
        $this->assertEquals(8, Task::find(2)->followers->count());
        $this->assertNotNull(1, Tweet::find(1)->tweep);

        $this->assertNotNull($followersTaskResponse['data'][0]['tweep']);

        $this->assertEquals($firstLikesTaskTweepsIds, $secondLikesTaskTweepsIds);

        $this->assertEquals($firstLikesTaskTweepsIdStrs, $secondLikesTaskTweepsIdStrs);
        $this->assertEquals($firstLikesTaskTweepsIdStrs, $followersTaskTweeps);

        $this->assertNotNull(Follower::find(1)->tweep);
    }
}
