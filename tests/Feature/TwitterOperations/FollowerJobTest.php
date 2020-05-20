<?php

namespace Tests\Feature\TwitterOperations;

use App\Follower;
use App\Jobs\CleanFollowersJob;
use App\Jobs\FetchFollowersJob;
use App\Task;
use Tests\Feature\TwitterOperations\Shared\UsersListTest;

class FollowerJobTest extends UsersListTest
{
    public function setUp() : void
    {
        parent::setUp();

        $this->jobName = FetchFollowersJob::class;
        $this->cleaningJobName = CleanFollowersJob::class;
        $this->apiEndpoint = '/api/followers';
        $this->twitterEndPoint = 'followers/list';
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
        return Follower::all();
    }

    protected function getFromTask($taskId)
    {
        return Task::find($taskId)->followers;
    }

    public function test_create_new_task_if_the_previous_task_completed()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');
        $this->bindTwitterConnector(['users'=>[], 'next_cursor_str'=>0]);
        $this->getJson('/api/followers')
        ->assertStatus(200);
        $this->fireJobsAndBindTwitter([]);
        $this->assertTaskCount(1, 'completed');
        $this->getJson('/api/followers')
        ->assertStatus(200);
        $this->assertTaskCount(2);
        for ($i = 1; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }
        $this->assertTaskCount(2, 'completed');
        $this->assertEquals('followers/list', $this->lastTwitterClientData()['endpoint']);
        $this->assertEquals($this->lastTwitterClientData()['parameters']['user_id'], auth()->user()->socialUsers[0]->social_user_id);
    }
}
