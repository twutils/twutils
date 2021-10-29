<?php

namespace Tests\Feature\TwitterOperations;

use App\Models\Task;
use App\Models\Tweep;
use App\Models\Following;
use App\Jobs\FetchFollowingJob;
use App\Jobs\CleanFollowingsJob;
use App\Jobs\FetchFollowingLookupsJob;
use Tests\Feature\TwitterOperations\Shared\UsersListTest;

class FollowingJobTest extends UsersListTest
{
    public function setUp(): void
    {
        parent::setUp();
        $this->jobName = FetchFollowingJob::class;
        $this->cleaningJobName = CleanFollowingsJob::class;
        $this->apiEndpoint = '/api/following';
        $this->twitterEndPoint = 'friends/list';
    }

    protected function assertBelongsToTask()
    {
        $this->assertEquals($this->getDBTable()->random()->task_id, Task::first()->id);
    }

    protected function fetchTwitterResponse($usersCount = 1, $nextCursorStr = 0, $startIndex = 0)
    {
        return $this->fetchFollowingResponse($usersCount, $nextCursorStr, $startIndex);
    }

    protected function getDBTable()
    {
        return Following::all();
    }

    protected function getFromTask($taskId)
    {
        return Task::find($taskId)->followings;
    }

    public function test_create_new_task_if_the_previous_task_completed()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');
        $this->bindTwitterConnector(['users'=>[], 'next_cursor_str'=>0]);
        $this->getJson('/api/following')
        ->assertStatus(200);
        $this->fireJobsAndBindTwitter([]);
        $this->assertTaskCount(1, 'completed');
        $this->getJson('/api/following')
        ->assertStatus(200);
        $this->assertTaskCount(2);
        for ($i = 1; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }
        $this->assertTaskCount(2, 'completed');
        $this->assertTrue($this->allTwitterClientData()->pluck('path')->contains('friends/list'));
        $this->assertTrue($this->allTwitterClientData()->pluck('parameters.user_id')->contains(auth()->user()->socialUsers[0]->social_user_id));
    }

    public function test_dispatch_lookups_job_at_the_end()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson('/api/following')
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $this->fetchFollowingResponse(10, 1234),
                ],
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $this->fetchFollowingResponse(10, 0, 10),
                ],
            ]
        );

        $this->assertCountDispatchedJobs(1, FetchFollowingLookupsJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Following::all()->count(), 20);
    }

    public function test_dispatch_lookups()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson('/api/following')
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $this->fetchFollowingResponse(10, 1234),
                ],
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $this->fetchFollowingResponse(10, 0, 10),
                ],
            ]
        );

        $this->assertCountDispatchedJobs(1, FetchFollowingLookupsJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Following::all()->count(), 20);
    }

    public function test_dispatch_lookups2()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson('/api/following')
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $this->fetchFollowingResponse(10, 0),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_1' => false, '_2' => true, '_4' => true]),
                ],
            ]
        );
        $tweepIds = Tweep::whereIn('id', [2, 4])->pluck('id_str')->toArray();

        $this->assertEquals('friendships/lookup', $this->lastTwitterClientData()['path']);
        $this->assertEquals('_1,_2,_3,_4,_5,_6,_7,_8,_9,_10', $this->lastTwitterClientData()['parameters']['user_id']);
        $this->assertEquals(2, Following::where('followed_by', true)->whereIn('tweep_id_str', $tweepIds)->get()->count());
        $this->assertCountDispatchedJobs(1, FetchFollowingLookupsJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Following::all()->count(), 10);
    }

    public function test_dispatch_lookups3()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson('/api/following')
        ->assertStatus(200);

        config(['twutils.twitter_requests_counts.fetch_following_lookups' => 2]);

        $fetchFollowingResponse = $this->fetchFollowingResponse(10, 0);
        $fetchFollowingResponse->users[5]->id_str = '_123';

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $fetchFollowingResponse,
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_1' => false, '_2' => true]),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_3' => false, '_4' => false]),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_5' => true, '_123' => true]),
                ],
            ]
        );

        $tweepIds = Tweep::whereIn('id_str', ['_2', '_123', '_5'])->pluck('id_str')->toArray();

        $this->assertEquals(3, Following::where('followed_by', true)->count());
        $this->assertEquals(3, Following::where('followed_by', true)->whereIn('tweep_id_str', $tweepIds)->get()->count());
        $this->assertCountDispatchedJobs(5, FetchFollowingLookupsJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Following::all()->count(), 10);
    }

    public function test_dispatch_lookups4()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson('/api/following')
        ->assertStatus(200);

        config(['twutils.twitter_requests_counts.fetch_following_lookups' => 2]);

        $fetchFollowingResponse = $this->fetchFollowingResponse(10, 0);
        $fetchFollowingResponse->users[5]->id_str = '_123';

        $exceptionIsThrown = false;

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $fetchFollowingResponse,
                ],
                [
                    'type'   => FetchFollowingLookupsJob::class,
                    'before' => function () {
                        app()->bind('AfterHTTPRequest', function () use (&$exceptionIsThrown) {
                            if (! $exceptionIsThrown) {
                                $exceptionIsThrown = true;

                                throw new \Abraham\TwitterOAuth\TwitterOAuthException('Error Processing Request', 1);
                            }
                        });
                    },
                    'twitterData'    => $this->getStub('rate_limit_response.json'),
                    'twitterHeaders' => ['x_rate_limit_remaining' => '0', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')],
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_1' => false, '_2' => true]),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_3' => false, '_4' => false]),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse(['_5' => true, '_123' => true]),
                ],
            ]
        );
        $tweepIds = Tweep::whereIn('id_str', ['_2', '_123', '_5'])->pluck('id_str')->toArray();

        $this->assertEquals(3, Following::where('followed_by', true)->count());
        $this->assertEquals(3, Following::where('followed_by', true)->whereIn('tweep_id_str', $tweepIds)->get()->count());
        $this->assertCountDispatchedJobs(6, FetchFollowingLookupsJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Following::all()->count(), 10);
    }
}
