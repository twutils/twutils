<?php

namespace Tests\Feature\TwitterOperations\Shared;

use DB;
use App\Task;
use App\Tweep;
use App\Tweet;
use App\Jobs\CleanLikesJob;
use App\Exports\TasksExport;
use Tests\TwitterClientMock;
use Illuminate\Support\Carbon;
use Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;

/*
 * A Generic abstract tests for all tasks that store tweets..
 */
abstract class TweetsTaskTest extends IntegrationTestCase
{
    protected $jobName;
    protected $apiEndpoint;
    protected $twitterEndpoint;

    public function test_basic_test()
    {
        Bus::fake();

        $this->bindTwitterConnector([]);
        $this->logInSocialUser('api');
        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);
        Bus::assertDispatched($this->jobName);
    }

    public function test_basic_delete_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->bindTwitterConnector($this->generateUniqueTweets(3));

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $taskId = $response->decodeResponseJson()['data']['task_id'];

        $this->fireJobsAndBindTwitter();

        $this->deleteJson("api/tasks/{$taskId}");

        $this->assertTaskCount(0);
        $this->assertCount(0, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_deleted_task_tweets_are_included_in_other_task()
    {
        $this->withoutJobs();

        // First User
        $this->logInSocialUser('api');

        $firstUser = auth()->user();
        $tweets = $this->generateUniqueTweetsAndTweeps(10, 10);

        // First User - Fetch 10 Tweets
        $this->bindTwitterConnector($tweets);
        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $firstTaskId = $response->decodeResponseJson()['data']['task_id'];

        $this->fireJobsAndBindTwitter();
        $lastJobIndex = count($this->dispatchedJobs);

        $this->assertCount(10, Tweet::all());

        // Second User
        $this->logInSocialUser('api');
        $secondUser = auth()->user();

        // Second User - Fetch 10 Tweets: 8 of the tweets are shared with first user's task, new 2 tweets/tweeps
        $tweets[0]->id_str = 123;
        $tweets[1]->id_str = 456;

        $freshTweeps = $this->generateUniqueTweeps(2, Tweep::count());
        $tweets[0]->user = $freshTweeps[0];
        $tweets[1]->user = $freshTweeps[1];

        $this->bindTwitterConnector($tweets);

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $secondTaskId = $response->decodeResponseJson()['data']['task_id'];
        $this->fireJobsAndBindTwitter([], $lastJobIndex);

        $this->assertCount(12, Tweet::all());
        $this->assertCount(12, Tweep::all());

        // Login First User - Delete the first task
        $this->actingAs($firstUser, 'api');

        $this->deleteJson("api/tasks/{$firstTaskId}");

        $this->assertTaskCount(1);

        // Assert that only 10 tweets remaining are related to second user's task
        $this->assertCount(10, Tweet::all());
        $this->assertLikesBelongsToTask();
        $this->assertEquals(10, Tweep::count());

        // Login Second User - Delete the second task
        $this->actingAs($secondUser, 'api');

        $this->deleteJson("api/tasks/{$secondTaskId}");

        $this->assertTaskCount(0);
        $this->assertCount(0, Tweet::all());
        $this->assertEquals(0, Tweep::count());
    }

    public function test_deleted_task_tweeps_are_included_in_other_followings_task()
    {
        $this->withoutJobs();

        // First User
        $this->logInSocialUser('api');

        $firstUser = auth()->user();
        $tweeps = $this->fetchFollowingResponse(10);

        // First User - Fetch 10 Following Tweeps
        $this->bindTwitterConnector($tweeps);
        $response = $this->getJson('/api/following');
        $response->assertStatus(200);
        $firstTaskId = $response->decodeResponseJson()['data']['task_id'];

        $this->fireJobsAndBindTwitter();
        $lastJobIndex = count($this->dispatchedJobs);

        $this->assertCount(10, Tweep::all());

        // Second User
        $this->logInSocialUser('api');
        $tweets = $this->generateUniqueTweetsAndTweeps(10, 10);
        $secondUser = auth()->user();

        // Second User - Fetch 10 Tweets: 8 of the tweets authors (tweeps) are included in the first user task
        $freshTweeps = $this->generateUniqueTweeps(2, Tweep::count());
        $tweets[0]->user = $freshTweeps[0];
        $tweets[1]->user = $freshTweeps[1];

        $this->bindTwitterConnector($tweets);

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $secondTaskId = $response->decodeResponseJson()['data']['task_id'];
        $this->fireJobsAndBindTwitter([], $lastJobIndex);

        $this->assertCount(10, Tweet::all());
        $this->assertCount(12, Tweep::all());

        // Second User - Delete the second task
        $this->deleteJson("api/tasks/{$secondTaskId}")->assertStatus(200);

        $this->assertTaskCount(1);

        // Assert that remaining tweeps are related to first user's task and the second user didn't impact them
        $this->assertCount(0, Tweet::all());
        $this->assertLikesBelongsToTask();
        $this->assertEquals(10, Tweep::count());

        // Login First User - Delete the first task
        $this->actingAs($firstUser, 'api');

        $this->deleteJson("api/tasks/{$firstTaskId}");

        $this->assertTaskCount(0);
        $this->assertCount(0, Tweet::all());
        $this->assertEquals(0, Tweep::count());
    }

    public function test_only_task_owner_can_delete_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->bindTwitterConnector($this->generateUniqueTweets(3));

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $taskId = $response->decodeResponseJson()['data']['task_id'];

        $this->fireJobsAndBindTwitter();

        $this->logInSocialUser('api');

        $response = $this->deleteJson("api/tasks/{$taskId}");

        $response->assertForbidden();

        $this->assertTaskCount(1);
        $this->assertCount(3, Tweet::all());
        $this->assertLikesBelongsToTask();
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

        $this->assertCountDispatchedJobs(1, $this->jobName);
        $this->assertTaskCount(1);
    }

    public function test_create_new_task_if_the_previous_task_completed()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->bindTwitterConnector([]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertTaskCount(1, 'completed');

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->assertTaskCount(2);

        for ($i = 1; $i < count($this->dispatchedJobs); $i++) {
            $this->dispatchedJobs[$i]->handle();
        }

        $this->assertTaskCount(2, 'completed');
    }

    public function test_basic_save_likes()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet, $tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type' => CleanLikesJob::class,
                    'skip' => true,
                ],
            ]
        );

        $this->assertTaskCount(1, 'completed');
        $this->assertCount(2, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_save_likes_with_custom_date()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweets(10);

        // TODO: Investigate why "postJson" works while there is
        // no "POST" method defined for the route.

        $this->postJson($this->apiEndpoint, [
            'settings' => [
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date'   => now()->subDays(3)->format('Y-m-d'),
            ],
        ])
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([
            [
                'type'           => $this->jobName,
                'twitterData'    => $tweets,
            ],
        ]);

        $this->assertTaskCount(1, 'completed');
        $this->assertCount(4, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_save_likes_has_correct_twitter_parameters()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        config()->set(['twutils.minimum_expected_likes' => 2]);
        config()->set(['twutils.twitter_requests_counts.fetch_likes' => 3]);

        $tweets = $this->generateUniqueTweets(10);

        $tweetsDividedForMultipleJobs = [];

        collect($tweets)
            ->chunk(config('twutils.twitter_requests_counts.fetch_likes'))
            ->map(function($tweetsChunk) use (& $tweetsDividedForMultipleJobs) {

                $tweetsDividedForMultipleJobs[] = [
                    'type'           => $this->jobName,
                    'twitterData'    => $tweetsChunk->toArray(),
                ];
            });

        $tweetsDividedForMultipleJobs[] = [
            'type'           => $this->jobName,
            'twitterData'    => [],
        ];

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter($tweetsDividedForMultipleJobs);

        $lastJobIndex = count($this->dispatchedJobs);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter($tweetsDividedForMultipleJobs, $lastJobIndex);

        $this->assertTaskCount(2, 'completed');
        $this->assertCount(10, Task::find(1)->likes->pluck('id_str'));
        $this->assertCount(10, Task::find(2)->likes);

        // Assert it's 8 Requests since we are requesting with parameter
        // count as '3' tweets while it's all 10 tweets.
        // So the 10 tweets will be fetched in 4 requests.
        // and we have 2 tasks, so it's 8 requests.
        $this->assertCount(
            8,
            TwitterClientMock::getAllCallsData(),
        );

        foreach (TwitterClientMock::getAllCallsData() as $index => $twitterCallData) {
            $this->assertEquals(
                $this->twitterEndpoint,
                $twitterCallData['endpoint'],
            );

            $expectedParameters = $this->initalTwitterParametersKeys;

            // 1st request and 5th are the first requests each was performed for a newly created task
            // So only them, aren't expected to have 'max_id' parameter

            if ( ! in_array($index, [0, 4])) {
                $expectedParameters = array_merge($this->initalTwitterParametersKeys, ['max_id']);
            }

            $this->assertEquals(
                $expectedParameters,
                array_keys($twitterCallData['parameters']),
                'Request [' . $index . '] to twitter doesn\'t contain the correct parameters'
            );
        }
    }

    public function test_save_likes_with_and_without_custom_date_all_has_correct_twitter_parameters()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        config()->set(['twutils.minimum_expected_likes' => 2]);
        config()->set(['twutils.twitter_requests_counts.fetch_likes' => 3]);

        $tweets = $this->generateUniqueTweets(10);

        $tweetsDividedForMultipleJobs = [];

        collect($tweets)
            ->chunk(config('twutils.twitter_requests_counts.fetch_likes'))
            ->map(function($tweetsChunk) use (& $tweetsDividedForMultipleJobs) {

                $tweetsDividedForMultipleJobs[] = [
                    'type'           => $this->jobName,
                    'twitterData'    => $tweetsChunk->toArray(),
                ];
            });

        $tweetsDividedForMultipleJobs[] = [
            'type'           => $this->jobName,
            'twitterData'    => [],
        ];

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter($tweetsDividedForMultipleJobs);

        $lastJobIndex = count($this->dispatchedJobs);

        $taskSettings = [
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date'   => now()->subDays(3)->format('Y-m-d'),
        ];

        $this->postJson($this->apiEndpoint, [
            'settings' => $taskSettings,
        ])
        ->assertStatus(200);

        $tweetsDividedForMultipleJobs = [];

        collect($tweets)
            ->filter(function ($tweet) use ($taskSettings) {
                    $shouldReturn = true;
                    $tweetCreatedAt =  Carbon::createFromTimestamp(strtotime($tweet->created_at ?? 1));

                    if (
                        isset($taskSettings['start_date']) &&
                        ! $tweetCreatedAt->greaterThanOrEqualTo($taskSettings['start_date'])
                    ) {
                        $shouldReturn = false;
                    }

                    if (
                        isset($taskSettings['end_date']) &&
                        ! $tweetCreatedAt->lessThanOrEqualTo($taskSettings['end_date'])
                    ) {
                        $shouldReturn = false;
                    }

                    return $shouldReturn;
            })
            ->chunk(config('twutils.twitter_requests_counts.fetch_likes'))
            ->map(function($tweetsChunk) use (& $tweetsDividedForMultipleJobs) {

                $tweetsDividedForMultipleJobs[] = [
                    'type'           => $this->jobName,
                    'twitterData'    => $tweetsChunk->toArray(),
                ];
            });

        $tweetsDividedForMultipleJobs[] = [
            'type'           => $this->jobName,
            'twitterData'    => [],
        ];

        $this->fireJobsAndBindTwitter($tweetsDividedForMultipleJobs, $lastJobIndex);

        $this->assertTaskCount(2, 'completed');
        $this->assertCount(10, Task::find(1)->likes->pluck('id_str'));
        $this->assertCount(4, Task::find(2)->likes);

        // Assert it's 6 Requests since we are requesting with parameter
        // count as '3' tweets while it's all 10 tweets.
        // So the first task 10 tweets will be fetched in 4 requests.
        // And the second task is parameterized to fetch only 
        // within 4 tweets that will be fetched in 3 requests.
        $this->assertCount(
            6,
            TwitterClientMock::getAllCallsData(),
        );

        foreach (TwitterClientMock::getAllCallsData() as $index => $twitterCallData) {
            $this->assertEquals(
                $this->twitterEndpoint,
                $twitterCallData['endpoint'],
            );

            $expectedParameters = $this->initalTwitterParametersKeys;

            // 1st request and 5th are the first requests each was performed for a newly created task
            // So only them, aren't expected to have 'max_id' parameter

            if ( $index !== 0 && $index < 4) {
                $expectedParameters = array_merge($this->initalTwitterParametersKeys, ['max_id']);
            } else if ($index >= 4) {
                $expectedParameters = array_merge($this->initalTwitterParametersKeys, ['since_id', 'max_id']);
            }

            $this->assertEquals(
                $expectedParameters,
                array_keys($twitterCallData['parameters']),
                'Request [' . $index . '] to twitter doesn\'t contain the correct parameters.'
                . json_encode($twitterCallData)
            );
        }
    }

    public function test_basic_save_tweets_first_request_has_error()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $exceptionIsThrown = false;

        $this->fireJobsAndBindTwitter([
            [
                'type'   => $this->jobName,
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
                'type'        => $this->jobName,
                'twitterData' => array_fill(0, 2, $tweet),
                'after'       => function ($job) {
                    $this->assertNotNull($job->delay);
                    $nextJobDelay = $this->dispatchedJobs[1]->delay->diffInSeconds(now());
                    $this->assertLessThanOrEqual(60, $nextJobDelay);
                },
            ],
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
            ],
        ]);

        $this->assertTaskCount(1, 'completed');
        $this->assertCount(2, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_build_next_job_if_needed()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        config()->set(['twutils.minimum_expected_likes' => 10]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 10, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 10, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 5, $tweet),
                ],
                [
                    'type' => CleanLikesJob::class,
                    'skip' => true,
                ],
            ]
        );

        $this->assertCountDispatchedJobs(3, $this->jobName);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(25, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_expired_token_in_middle_of_fetch()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');
        $expiredTokenStub = $this->getStub('expired_token.json');

        config()->set(['twutils.minimum_expected_likes' => 10]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 10, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $expiredTokenStub,
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 10, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => [],
                ],
                [
                    'type' => CleanLikesJob::class,
                    'skip' => true,
                ],
            ]
        );

        $this->assertCountDispatchedJobs(2, $this->jobName);
        $this->assertTaskCount(1, 'broken');
        $this->assertCount(10, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_next_job_returned_zero_tweets()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        config()->set(['twutils.minimum_expected_likes' => 10]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 10, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 10, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => [],
                ],
                [
                    'type' => CleanLikesJob::class,
                    'skip' => true,
                ],
            ]
        );

        $this->assertCountDispatchedJobs(3, $this->jobName);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(20, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_delay_next_job_if_needed()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        config()->set(['twutils.minimum_expected_likes' => 10]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'           => $this->jobName,
                    'twitterData'    => array_fill(0, 15, $tweet),
                    'twitterHeaders' => ['x_rate_limit_remaining' => '1', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')],
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => [$tweet],
                    'after'       => function ($job) {
                        $this->assertNotNull($job->delay);
                        $nextJobDelay = $this->dispatchedJobs[1]->delay->diffInSeconds(now());
                        $this->assertLessThanOrEqual(60, $nextJobDelay);
                    },
                ],
                [
                    'type' => CleanLikesJob::class,
                    'skip' => true,
                ],
            ]
        );

        $this->assertCountDispatchedJobs(2, $this->jobName);
        $this->assertCount(16, Tweet::all());

        $this->assertTaskCount(1, 'completed');

        $this->assertLikesBelongsToTask();
    }

    public function test_dont_build_next_job_if_less_than100()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        config()->set(['twutils.minimum_expected_likes' => 10]);

        $this->bindTwitterConnector($this->generateUniqueTweets(4));

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $this->assertTrue(empty($this->dispatchedJobs[1]) || get_class($this->dispatchedJobs[1]) !== $this->jobName);

        $this->assertCountDispatchedJobs(1, $this->jobName);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(4, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_dont_build_next_job_if_less_than100and_ignore_delay()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector(array_fill(0, 3, $tweet), ['x_rate_limit_remaining' => '1', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type' => CleanLikesJob::class,
                    'skip' => true,
                ],
            ]
        );

        $this->assertTrue(empty($this->dispatchedJobs[1]) || get_class($this->dispatchedJobs[1]) !== $this->jobName);

        $this->assertCountDispatchedJobs(1, $this->jobName);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(3, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_error_response()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('rate_limit_response.json');

        $this->bindTwitterConnector($tweet, ['x_rate_limit_remaining' => '0', 'x_rate_limit_reset' => now()->addSeconds(60)->format('U')]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->dispatchedJobs[0]->handle();

        $this->assertTrue(empty($this->dispatchedJobs[1]));

        $this->assertCountDispatchedJobs(1, $this->jobName);
        $this->assertTaskCount(1, 'broken');
        $this->assertCount(0, Tweet::all());
    }

    public function test_clean_likes_job_remove_duplicate_str_id()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector(array_fill(0, 3, $tweet));

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertCountDispatchedJobs(1, CleanLikesJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(1, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_clean_likes_job_remove_duplicate_assignment_to_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector(array_fill(0, 3, $tweet));

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertCountDispatchedJobs(1, CleanLikesJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(1, Tweet::all());
        $this->assertCount(1, Task::first()->likes);
        $this->assertCount(1, DB::table('task_tweet')->get());
        $this->assertLikesBelongsToTask();
    }

    public function test_clean_likes_job_remove_duplicate_tweet_ids_to_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        config()->set(['twutils.minimum_expected_likes' => 2]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 2, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 2, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => [],
                ],
                [
                    'type' => CleanLikesJob::class,
                    'skip' => false,
                ],
            ]
        );

        $this->assertCountDispatchedJobs(1, CleanLikesJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(1, Tweet::all());
        $this->assertCount(1, DB::table('task_tweet')->get());
        $this->assertCount(1, Task::first()->likes);
        $this->assertLikesBelongsToTask();
    }

    public function test_clean_likes_job_remove_multiple_duplicate_tweet_ids_to_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');
        $secondTweet = $this->getStub('tweet.json');
        $secondTweet->id_str = '2';

        config()->set(['twutils.minimum_expected_likes' => 2]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 3, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 3, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 3, $tweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 3, $secondTweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => array_fill(0, 3, $secondTweet),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => [],
                ],
                [
                    'type' => CleanLikesJob::class,
                    'skip' => false,
                ],
            ]
        );

        $this->assertCountDispatchedJobs(1, CleanLikesJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(2, Tweet::all());
        $this->assertCount(2, DB::table('task_tweet')->get());
        $this->assertCount(2, Task::first()->likes);
        $this->assertLikesBelongsToTask();
    }

    public function test_clean_likes_job_remove_duplicate_str_id_two_str_ids()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');
        $tweets = array_fill(0, 2, (array) $tweet);

        $tweets[1]['id_str'] = '123';

        foreach ($tweets as $index => $tweet) {
            $tweets[$index] = (object) $tweet;
        }

        $this->bindTwitterConnector($tweets);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertCountDispatchedJobs(1, CleanLikesJob::class);
        $this->assertTaskCount(1, 'completed');
        $this->assertCount(2, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_clean_likes_job_remove_duplicate_str_id_multiple_str_ids()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');
        $tweets = array_fill(0, 10, (array) $tweet);

        $tweets[2]['id_str'] = '123';
        $tweets[3]['id_str'] = '123';
        $tweets[4]['id_str'] = '123';

        $tweets[5]['id_str'] = '456';
        $tweets[6]['id_str'] = '456';

        $tweets[7]['id_str'] = '789';

        foreach ($tweets as $index => $tweet) {
            $tweets[$index] = (object) $tweet;
        }

        $this->bindTwitterConnector($tweets);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->dispatchedJobs[0]->handle();
        $this->assertCountDispatchedJobs(3, null);

        $this->dispatchedJobs[1]->handle();
        $this->dispatchedJobs[2]->handle();
        $this->dispatchedJobs[3]->handle();
        $this->dispatchedJobs[4]->handle();

        $this->assertTaskCount(1, 'completed');
        $this->assertCount(4, Tweet::all());
        $this->assertLikesBelongsToTask();
    }

    public function test_fetch_extended_entities()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTrue($this->lastTwitterClientData()['parameters']['include_entities']);
    }

    public function test_limit_tasks_per_user()
    {
        static $lastJobIndex = 0;
        $this->withoutJobs();
        $this->logInSocialUser('api');
        config()->set(['twutils.tasks_limit_per_user' => 3]);
        $this->bindTwitterConnector([]);

        for ($i = 0; $i < 3; $i++) {
            $response = $this->getJson($this->apiEndpoint);
            $response->assertStatus(200);
            $this->fireJobsAndBindTwitter([], $lastJobIndex);
            $lastJobIndex = count($this->dispatchedJobs);
        }

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(422);

        $this->assertCount(3, \App\Task::all());
    }

    public function test_no_duplicate_tweeps()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->bindTwitterConnector($this->generateUniqueTweetsAndTweeps(10, 8));

        $response = $this->getJson($this->apiEndpoint);
        $response->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertCount(1, Task::all());
        $this->assertEquals('completed', Task::find(1)->status);
        $this->assertCount(8, Tweep::all());
        $this->assertCount(10, Tweet::all());
        $this->assertCount(8, Tweep::whereIn('id_str', Tweet::all()->pluck('tweep_id_str')->toArray())->get());
    }

    public function test_basic_export_tweets()
    {
        Excel::fake();

        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweets(5);

        $this->bindTwitterConnector($tweets);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $response = $this->get('task/1/download');
        $response->assertStatus(200);

        $response = $this->get('task/1/download/excel');
        $response->assertStatus(200);

        $fileName = auth()->user()->socialUsers[0]->nickname.'_'.Task::find(1)->shortName.'_'.Task::find(1)->created_at->format('m-d-Y_hia').'.xlsx';

        $tasksExportsCalled = false;

        Excel::assertDownloaded($fileName, function (TasksExport $taskExport) use (&$tasksExportsCalled) {
            $tasksExportsCalled = true;
            $exportedTweets = $taskExport->collection();

            $this->assertCount(5, $exportedTweets);

            return true;
        });

        $this->assertTrue($tasksExportsCalled);
    }

    public function test_basic_export_tweets_html()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweets = $this->generateUniqueTweets(5);

        $this->bindTwitterConnector($tweets);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter();

        $response = $this->get('task/1/download/html');
        $response->assertStatus(200);

        $fileAsString = $response->streamedContent();

        $zipFile = new \PhpZip\ZipFile();
        $zipFile->openFromString($fileAsString);

        $zippedFiles = $zipFile->getListFiles();

        $this->assertContains('index.html', $zippedFiles);
        $this->assertContains('assets/build_css/app.css', $zippedFiles);
        $this->assertContains('assets/js/app.js', $zippedFiles);
    }
}
