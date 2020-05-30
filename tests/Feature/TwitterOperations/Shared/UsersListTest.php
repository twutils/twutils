<?php

namespace Tests\Feature\TwitterOperations\Shared;

use App\Tweep;
use Illuminate\Support\Str;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Bus;

abstract class UsersListTest extends IntegrationTestCase
{
    protected $jobName;
    protected $cleaningJobName;
    protected $apiEndpoint;
    protected $twitterEndPoint;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_basic_test()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);
        Bus::assertDispatched($this->jobName);
    }

    public function test_dont_create_same_operation_task_before_finishing_the_one_before()
    {
        $this->withoutJobs();

        $this->logInSocialUser('api');

        $this->bindTwitterConnector([]);

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(422);

        $this->assertCountDispatchedJobs(1);
        $this->assertTaskCount(1);
    }

    public function test_basic_save_follower()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->bindTwitterConnector($this->fetchTwitterResponse(2));

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals($this->getDBTable()->count(), 2);
        $this->assertBelongsToTask();
    }

    public function test_build_next_job_if_needed()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 1234),
                    'after'       => function () {
                        $this->assertEquals($this->lastTwitterClientData()['parameters']['cursor'], -1);
                    },
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 123, 10),
                    'after'       => function () {
                        $this->assertEquals($this->lastTwitterClientData()['parameters']['cursor'], 1234);
                    },
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 0, 20),
                    'after'       => function () {
                        $this->assertEquals($this->lastTwitterClientData()['parameters']['cursor'], 123);
                    },
                ],
            ]
        );

        $this->assertCountDispatchedJobs(3);
        $this->assertTaskCount(1, 'completed');
        $this->assertEquals($this->getDBTable()->count(), 30);
        $this->assertBelongsToTask();
    }

    public function test_tweep_is_updated_on_the_next_fetch()
    {
        $this->withoutJobs();
        $usersList = $this->fetchTwitterResponse(2); // followers
        $url = Str::random(10); // the data that will be updated in the next followers fetch

        // First Fetch
        $this->logInSocialUser('api');

        $this->bindTwitterConnector($usersList);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals($this->getDBTable()->count(), 2);
        $this->assertBelongsToTask();

        // second fetch, different user
        $lastDisptachedJobs = count($this->dispatchedJobs);
        $this->logInSocialUser('api', ['username'=> 'bar2']);

        $usersList->users[0]->url = $url;

        $this->bindTwitterConnector($usersList);

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $taskId = json_decode($response->getContent())->data->task_id;

        $this->fireJobsAndBindTwitter([], $lastDisptachedJobs);

        $tweep = Tweep::where('id_str', $usersList->users[0]->id_str)->first();
        $this->assertEquals($url, $tweep->url);

        // generic assertion when the two fetch follower tasks is completed
        $this->assertTaskCount(2, 'completed');
        $this->assertEquals($this->getDBTable()->count(), 4);
        $this->assertEquals($this->getFromTask($taskId)->count(), 2);
    }

    public function test_dispatch_lookups_job_at_the_end()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 1234),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 0, 10),
                ],
            ]
        );

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals($this->getDBTable()->count(), 20);
    }

    public function test_dispatch_clean_followers_job_at_the_end()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 1234),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->fetchTwitterResponse(10, 0),
                ],
            ]
        );

        $this->assertCountDispatchedJobs(2, $this->cleaningJobName);
        $this->assertEquals($this->getDBTable()->count(), 10);
    }

    // test: response doesn't have 'next_cursor_str' or 'users'
    // test: catch 'TwitterOAuthException' and twitter connectivity exceptions
    // test: after catching 'TwitterOAuthException', the rebuilt job should be delayed
    // test: should include user's location, followers count, followers count, is verified..

    protected function assertCountDispatchedJobs($count, $jobName = null)
    {
        parent::assertCountDispatchedJobs($count, $jobName ?? $this->jobName);
    }
}
