<?php

namespace Tests;

use Mockery;
use App\Task;
use App\User;
use App\SocialUser;
use App\Jobs\FetchLikesJob;
use App\TwUtils\ITwitterConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationTestCase extends TestCase
{
    use RefreshDatabase {
        RefreshDatabase::refreshDatabase as refreshDatabaseTrait;
     }

    public function setUp(): void
    {
        parent::setUp();
        $this->bindTwitterConnector();
        $this->clearTwitterClientData();
        config()->set('twutils.minimum_expected_likes', 100);
    }

    public function refreshDatabase()
    {
        $this->refreshDatabaseTrait();
        \DB::getSchemaBuilder()->enableForeignKeyConstraints();
        if (config('database.connections.'.config('database.default').'.driver') == 'sqlite') {
            \DB::connection()->getPdo()->exec('pragma foreign_keys=1');
        }
    }

    protected function assertTaskCount($count, $status = null)
    {
        $this->assertEquals(Task::all()->count(), $count);
        if ($status) {
            Task::all()->each(
                function ($task) use ($status) {
                    $this->assertEquals($status, $task->status);
                }
            );
        }
    }

    protected function uniqueTweetIds($tweets)
    {
        static $counter = 0;

        return collect($tweets)
        ->map(
            function ($tweet) use (&$counter) {
                $tweet = clone $tweet;
                $tweet->id = $counter;
                $tweet->id_str = $counter++;

                return $tweet;
            }
        )
        ->toArray();
    }

    protected function uniqueTweetDates($tweets)
    {
        $counter = 0;

        return collect($tweets)
        ->map(
            function ($tweet) use (&$counter) {
                $tweet->created_at = now()->subDays($counter++)->format('D M d h:m:s O Y');

                return $tweet;
            }
        )
        ->toArray();
    }

    protected function generateTweets($count, $createdAtDate = null)
    {
        $tweet = $this->getStub('tweet.json');
        $tweets = array_fill(0, $count, (array) $tweet);

        for ($i = 0; $i < $count; $i++) {
            $tweets[$i]['id_str'] = (string) $i;

            $tweets[$i]['created_at'] = $createdAtDate ?? $tweets[$i]['created_at'];
        }

        foreach ($tweets as $index => $tweet) {
            $tweets[$index] = (object) $tweet;
        }

        return $tweets;
    }

    protected function bindMultipleTweets($count = 40, $createdAtDate = 'Tue Sep 04 15:55:52 +0000 2012')
    {
        $tweets = $this->generateTweets($count, $createdAtDate);

        $this->bindTwitterConnector($tweets);
    }

    protected function generateUniqueTweets(int $count, object $tweetSample = null)
    {
        if (is_null($tweetSample)) {
            $tweetSample = $this->getStub('tweet.json');
        }

        return $this->uniqueTweetDates($this->uniqueTweetIds(array_fill(0, $count, $tweetSample)));
    }

    protected function assertCountDispatchedJobs($count, $jobClass = FetchLikesJob::class)
    {
        $dispatchedJobs = collect($this->dispatchedJobs);

        if (! is_null($jobClass)) {
            $dispatchedJobs = $dispatchedJobs->filter(
                function ($dispatchedJob) use ($jobClass) {
                    return get_class($dispatchedJob) == $jobClass;
                }
            );
        }

        $this->assertEquals($count, $dispatchedJobs->count());
    }

    protected function assertLikesBelongsToTask()
    {
        $tasksIds = \DB::table('task_tweet')->get()->pluck('task_id')->unique();

        foreach ($tasksIds as $taskId) {
            $this->assertEquals($taskId, Task::first()->id);
        }
    }

    protected function bindTwitterConnector($twitterResults = [], $twitterHeaders = ['x_rate_limit_remaining' => '74'], $callback = null)
    {
        $twitterClient = new TwitterClientMock($twitterResults, $twitterHeaders);
        $twitterConnector = Mockery::mock(ITwitterConnector::class);
        $twitterConnector->shouldReceive('get')
        ->andReturn($twitterClient);

        if (! is_null($callback)) {
            $callback($twitterConnector, $twitterClient);
        }

        app()->bind(
            ITwitterConnector::class,
            function () use ($twitterConnector) {
                return $twitterConnector;
            }
        );
    }

    protected function lastTwitterClientData()
    {
        return TwitterClientMock::getLastCallData();
    }

    protected function allTwitterClientData()
    {
        return TwitterClientMock::getAllCallsData();
    }

    protected function clearTwitterClientData()
    {
        return TwitterClientMock::clearCallsData();
    }

    protected function logInSocialUser($driver = null, $userOverride = [])
    {
        $appUser = factory(User::class)->create($userOverride);

        $this->actingAs($appUser, $driver);

        factory(SocialUser::class)->create(['user_id' => $appUser->id]);
    }

    protected function logInSocialUserForDestroyTweets()
    {
        $this->logInSocialUserForDestroyLikes();
    }

    protected function logInSocialUserForDestroyLikes()
    {
        $this->logInSocialUser('api');
        collect(SocialUser::all())
        ->each(
            function ($socialUser) {
                $socialUser->scope = ['read', 'write'];
                $socialUser->save();
            }
        );
    }

    protected function fireJobsAndBindTwitter($data = [], $startIndex = 0)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $value + ['called' => false];
        }

        for ($i = $startIndex; $i < count($this->dispatchedJobs); $i++) {
            $queuedJob = $this->dispatchedJobs[$i];
            $jobDataHolder = null;
            $jobDataHolderIndex = null;

            foreach ($data as $index => $jobDetails) {
                if (! $jobDetails['called']) {
                    $jobDataHolderIndex = $index;
                    $jobDataHolder = $jobDetails;
                    break;
                }
            }

            if ((! is_null($jobDataHolder)) && get_class($queuedJob) == $jobDataHolder['type'] && isset($jobDataHolder['twitterData'])) {
                $this->bindTwitterConnector($jobDataHolder['twitterData'], $jobDataHolder['twitterHeaders'] ?? []);
            }

            if ((! is_null($jobDataHolder)) && get_class($queuedJob) == $jobDataHolder['type'] && isset($jobDataHolder['before'])) {
                $jobDataHolder['before']($queuedJob);
            }

            if ((! is_null($jobDataHolder)) && get_class($queuedJob) == $jobDataHolder['type']) {
                $data[$jobDataHolderIndex]['called'] = true;
            }

            if ((! is_null($jobDataHolder)) && get_class($queuedJob) == $jobDataHolder['type'] && isset($jobDataHolder['skip']) && $jobDataHolder['skip']) {
                continue;
            }

            if (in_array('--debug', $_SERVER['argv'], true)) {
                dump('Running '.get_class($queuedJob));
            }

            $queuedJob->handle();

            if ((! is_null($jobDataHolder)) && get_class($queuedJob) == $jobDataHolder['type'] && isset($jobDataHolder['after'])) {
                $jobDataHolder['after']($queuedJob);
            }
        }
    }

    protected function fetchFollowingLookupsResponse($data = [])
    {
        $stub = ['id_str' => null,
            'connections' => [
                'following',
            ],
        ];

        $results = [];

        foreach ($data as $id => $followedBy) {
            $lookup = $stub;
            $lookup['id_str'] = (string) $id;
            if ($followedBy) {
                $lookup['connections'][] = 'followed_by';
            }

            $results[] = $lookup;
        }

        return $results;
    }

    protected function generateUniqueTweetsAndTweeps($tweetsCount = 1, $usersCount = 1, $tweetSample = null, $tweepsStartIndex = 0)
    {
        $tweets = $this->generateUniqueTweets($tweetsCount, $tweetSample);
        $tweeps = $this->generateUniqueTweeps($usersCount, $tweepsStartIndex);

        $onholdTweepsCounter = count($tweeps);

        for ($i = 0; $i < count($tweets); $i++) {
            if ($onholdTweepsCounter !== 0) {
                $tweets[$i]->user = $tweeps[--$onholdTweepsCounter];
            } else {
                $tweets[$i]->user = $tweeps[rand(0, count($tweeps) - 1)];
            }
        }

        return $tweets;
    }

    protected function generateUniqueTweeps($usersCount = 1, $startIndex = 0): array
    {
        $faker = app(\Faker\Generator::class);
        $user = $this->getStub('fetch_following_users.json');
        $users = array_fill(0, $usersCount, (array) $user);

        for ($i = 0; $i < $usersCount; $i++) {
            $users[$i]['id'] = $i + 1 + $startIndex;
            $users[$i]['id_str'] = '_'.($i + 1 + $startIndex);
            $users[$i]['name'] = (string) $faker->name;
            $users[$i]['description'] = (string) implode(' ', $faker->sentences);
            $users[$i]['profile_banner_url'] = (string) $faker->imageUrl;
            $users[$i]['profile_image_url'] = (string) $faker->imageUrl;
            $users[$i]['profile_image_url_https'] = (string) $faker->imageUrl;
            $users[$i]['profile_background_color'] = (string) substr($faker->hexColor, 1);
            $users[$i]['screen_name'] = (string) $faker->username;
            $users[$i]['followers_count'] = (int) $faker->randomNumber();
            $users[$i]['friends_count'] = (int) $faker->randomNumber();
            $users[$i]['favourites_count'] = (int) $faker->randomNumber();
            $users[$i]['statuses_count'] = (int) $faker->randomNumber();
        }

        return collect($users)->map(function ($user) {
            return (object) $user;
        })->toArray();
    }

    protected function fetchFollowingResponse($usersCount = 1, $nextCursorStr = 0, $startIndex = 0)
    {
        $response = $this->getStub('fetch_following_response.json');
        $response->users = $this->generateUniqueTweeps($usersCount, $startIndex);

        foreach ($response->users as $index => $userArray) {
            $response->users[$index] = (object) $userArray;
        }

        $response->next_cursor_str = $nextCursorStr;

        return $response;
    }

    protected function getStub($stub)
    {
        return json_decode(file_get_contents(__DIR__.'/_stubs/'.$stub));
    }
}
