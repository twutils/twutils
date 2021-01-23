<?php

namespace Tests\TestData;

use App\SocialUser;
use App\Jobs\FetchLikesJob;
use App\TwUtils\UserManager;
use Tests\IntegrationTestCase;
use App\Jobs\FetchFollowersJob;
use App\Jobs\FetchFollowingJob;
use App\Jobs\FetchUserTweetsJob;
use App\Jobs\FetchFollowingLookupsJob;

class TestDataTest extends IntegrationTestCase
{
    public function testClientDataHasUserInfo()
    {
        $this->logInSocialUser('web');
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertNotNull(auth()->user());
        $this->assertStringContainsString(json_encode(auth()->user(), JSON_HEX_APOS), $response->getContent());
    }

    public function testCreateTestData()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');
        $testDataPath = base_path(json_decode(file_get_contents(base_path('package.json')))->twutils->testDataPath);
        $testData = $this->getTestData();
        file_put_contents($testDataPath, json_encode($testData, JSON_PRETTY_PRINT));
        $this->assertTrue(file_exists($testDataPath));
    }

    protected function getTestData()
    {
        $fetchFollowing = $this->fetchFollowing();
        $fetchFollowers = $this->fetchFollowers();
        $fetchLikes = $this->fetchLikes();
        $fetchUserTweets = $this->fetchUserTweets();
        $tasksList = $this->tasksList();

        SocialUser::find(1)->update([
            'followers_count'  => rand(10, 2000),
            'favourites_count' => rand(10, 2000),
            'friends_count'    => rand(10, 2000),
            'statuses_count'   => rand(10, 2000),
        ]);

        return [
            'Tasks' => [
                'TasksList'       => $tasksList,
                'FetchLikes'      => $fetchLikes,
                'FetchFollowing'  => $fetchFollowing,
                'FetchFollowers'  => $fetchFollowers,
                'FetchUserTweets' => $fetchUserTweets,
            ],
            'clientData' => UserManager::getClientData(),
        ];
    }

    protected function tasksList()
    {
        return $this->getJson('api/tasks')->decodeResponseJson();
    }

    protected function fetchLikes()
    {
        $lastFiredJobIndex = count($this->dispatchedJobs);
        $response = $this->getJson('/api/likes');
        $response->assertStatus(200);
        $response = $response->decodeResponseJson();

        $tweets = $this->generateUniqueTweets(10);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchLikesJob::class,
                    'twitterData' => $tweets,
                ],
            ],
            $lastFiredJobIndex
        );

        return [
            'TaskResponse'       => $this->getJson('/api/tasks/'.$response['data']['task_id'])->decodeResponseJson(),
            'TaskDataResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/data')->decodeResponseJson(),
            'TaskViewResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/view')->decodeResponseJson(),
            'CreateTaskResponse' => $response,
        ];
    }

    protected function fetchUserTweets()
    {
        $lastFiredJobIndex = count($this->dispatchedJobs);
        $response = $this->getJson('/api/userTweets');
        $response->assertStatus(200);
        $response = $response->decodeResponseJson();

        $tweets = $this->generateUniqueTweets(10);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchUserTweetsJob::class,
                    'twitterData' => $tweets,
                ],
            ],
            $lastFiredJobIndex
        );

        return [
            'TaskResponse'       => $this->getJson('/api/tasks/'.$response['data']['task_id'])->decodeResponseJson(),
            'TaskDataResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/data')->decodeResponseJson(),
            'TaskViewResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/view')->decodeResponseJson(),
            'CreateTaskResponse' => $response,
        ];
    }

    protected function fetchFollowing()
    {
        $response = $this->getJson('/api/following');
        $response->assertStatus(200);
        $response = $response->decodeResponseJson();

        config(['twutils.twitter_requests_counts.fetch_following_lookups' => 2]);

        $fetchFollowingResponse = $this->fetchFollowingResponse(10, 0);
        $fetchFollowingResponse->users[5]->id_str = 123;

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowingJob::class,
                    'twitterData' => $fetchFollowingResponse,
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse([1 => false, 2 => true]),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse([3 => false, 4 => false]),
                ],
                [
                    'type'        => FetchFollowingLookupsJob::class,
                    'twitterData' => $this->fetchFollowingLookupsResponse([5 => true, 123 => true]),
                ],
            ]
        );

        return [
            'TaskResponse'       => $this->getJson('/api/tasks/'.$response['data']['task_id'])->decodeResponseJson(),
            'TaskDataResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/data')->decodeResponseJson(),
            'TaskViewResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/view')->decodeResponseJson(),
            'CreateTaskResponse' => $response,
        ];
    }

    protected function fetchFollowers()
    {
        $lastFiredJobIndex = count($this->dispatchedJobs);
        $response = $this->getJson('/api/followers');
        $response->assertStatus(200);
        $response = $response->decodeResponseJson();

        $fetchFollowingResponse = $this->fetchFollowingResponse(10, 0);
        $fetchFollowingResponse->users[5]->id_str = 123;

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => FetchFollowersJob::class,
                    'twitterData' => $fetchFollowingResponse,
                ],
            ],
            $lastFiredJobIndex
        );

        return [
            'TaskResponse'       => $this->getJson('/api/tasks/'.$response['data']['task_id'])->decodeResponseJson(),
            'TaskDataResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/data')->decodeResponseJson(),
            'TaskViewResponse'   => $this->getJson('/api/tasks/'.$response['data']['task_id'].'/view')->decodeResponseJson(),
            'CreateTaskResponse' => $response,
        ];
    }
}
