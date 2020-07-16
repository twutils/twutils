<?php

namespace Tests\Feature\TwitterOperations\Shared;

use Config;
use App\Task;
use App\Tweet;
use Tests\TwitterClientMock;
use Illuminate\Support\Carbon;
use Tests\IntegrationTestCase;
use App\Jobs\SaveTweetMediaJob;
use Illuminate\Support\Facades\Bus;
use Tests\HttpClientMock;

/*
 * A Generic abstract tests for all tasks that store and attach entities and
 * deal with files.
 */
abstract class EntitiesTaskTests extends IntegrationTestCase
{
    protected $jobName;
    protected $apiEndpoint;
    protected $twitterEndpoint;
    protected $initalTwitterParametersKeys;

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

    public function test_basic_save_photo()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_one_photo.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        dd('Done.. test_basic_save_photo');

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][0], $tweet->id_str.'_1.jpeg');
    }

    public function test_basic_save_two_photos()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_two_photos.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_2.jpeg');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][0], $tweet->id_str.'_1.jpeg');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][1][0], $tweet->id_str.'_2.jpeg');
    }

    public function test_basic_save_gif()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_animated_gif.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_2.mp4');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][0], $tweet->id_str.'_1.jpeg');
    }

    public function test_basic_save_video()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_video.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_2.mp4');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][0], $tweet->id_str.'_1.jpeg');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][1], $tweet->id_str.'_2.mp4');

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

    public function test_dont_include_if_couldnt_save()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_one_photo.json');
        $tweet->extended_entities->media[0]->media_url_https = 'broken_file.zip';

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedMissing('1', $tweet->id_str.'_1.zip');
        $this->assertTrue(empty(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0]));
    }

    public function test_mixed_types_of_tweets()
    {
        $expectedSavedPaths = '1/media/11_1.jpeg,1/media/12_1.jpeg,1/media/13_1.jpeg,1/media/14_1.jpeg,1/media/15_1.jpeg,1/media/16_1.jpeg,1/media/17_1.jpeg,1/media/18_1.jpeg,1/media/19_1.jpeg,1/media/20_1.jpeg,1/media/20_2.jpeg,1/media/21_1.jpeg,1/media/21_2.jpeg,1/media/22_1.jpeg,1/media/22_2.jpeg,1/media/23_1.jpeg,1/media/23_2.jpeg,1/media/24_1.jpeg,1/media/24_2.jpeg,1/media/25_1.jpeg,1/media/25_2.jpeg,1/media/26_1.jpeg,1/media/26_2.jpeg,1/media/27_1.jpeg,1/media/27_2.jpeg,1/media/28_1.jpeg,1/media/28_2.jpeg,1/media/29_1.jpeg,1/media/29_2.jpeg,1/media/30_1.jpeg,1/media/30_2.mp4,1/media/31_1.jpeg,1/media/31_2.mp4,1/media/32_1.jpeg,1/media/32_2.mp4,1/media/33_1.jpeg,1/media/33_2.mp4';

        $expectedTweetsAttachmentsPaths = '11_1.jpeg,12_1.jpeg,13_1.jpeg,14_1.jpeg,15_1.jpeg,16_1.jpeg,17_1.jpeg,18_1.jpeg,19_1.jpeg,20_1.jpeg,20_2.jpeg,21_1.jpeg,21_2.jpeg,22_1.jpeg,22_2.jpeg,23_1.jpeg,23_2.jpeg,24_1.jpeg,24_2.jpeg,25_1.jpeg,25_2.jpeg,26_1.jpeg,26_2.jpeg,27_1.jpeg,27_2.jpeg,28_1.jpeg,28_2.jpeg,29_1.jpeg,29_2.jpeg,30_1.jpeg,30_2.mp4,31_1.jpeg,31_2.mp4,32_1.jpeg,32_2.mp4,33_1.jpeg,33_2.mp4';

        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweetWithOnePhoto = $this->getStub('tweet_with_one_photo.json');
        $tweetWithTwoPhotos = $this->getStub('tweet_with_two_photos.json');
        $tweetWithVideo = $this->getStub('tweet_with_video.json');
        $tweetWithGif = $this->getStub('tweet_with_animated_gif.json');

        config()->set(['twutils.minimum_expected_likes' => 10]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter(
            [
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->generateUniqueTweets(10),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->generateUniqueTweets(10, $tweetWithOnePhoto),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->generateUniqueTweets(10, $tweetWithTwoPhotos),
                ],
                [
                    'type'        => $this->jobName,
                    'twitterData' => $this->uniqueTweetIds([$tweetWithGif, $tweetWithVideo, $tweetWithGif, $tweetWithVideo]),
                ],
                [
                    'type'        => SaveTweetMediaJob::class,
                    'twitterData' => [],
                    'before'      => function () {
                        app('HttpClient')->throwException(1);
                    }
                ],
            ]
        );

        $likeEntitiesPaths = '';
        Task::all()->last()->tweets->sortBy('id')->pluck('pivot.attachments.paths')
        ->map(
            function ($likeEntityPaths) use (&$likeEntitiesPaths) {
                foreach ((array) $likeEntityPaths as $path) {
                    $likeEntitiesPaths .= implode(',', $path).',';
                }
            }
        );

        $likeEntitiesPaths = substr($likeEntitiesPaths, 0, -1);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 34);
        $this->assertLikesBelongsToTask();
        $this->assertStringContainsString($expectedSavedPaths, collect($this->getZippedFiles(1))->implode(','));
        $this->assertStringContainsString($expectedTweetsAttachmentsPaths, $likeEntitiesPaths);

        $this->assertNotContains(
            null,
            Task::all()->last()->tweets
                ->pluck('pivot.attachments')
                ->filter(function($attachments) {
                    return ! empty($attachments);
                })
                ->pluck('type'),
            '\'attachments\' column in \'task_tweet\' table can\'t be empty'
        );
    }

    public function test_do_nothing_with_regualr_tweets()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedMissing('1', $tweet->id_str.'_1.jpeg');
        $this->assertTrue(empty(Task::all()->last()->tweets->first()->pivot->attachments));
    }

    public function test_save_likes_with_and_without_custom_date_all_has_correct_twitter_parameters()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        config()->set(['twutils.minimum_expected_likes' => 2]);
        config()->set(['twutils.twitter_requests_counts.fetch_likes' => 3]);

        $tweets = $this->generateUniqueTweets(30);

        $tweetsDividedForMultipleJobs = [];

        collect($tweets)
            ->chunk(config('twutils.twitter_requests_counts.fetch_likes'))
            ->map(function ($tweetsChunk) use (&$tweetsDividedForMultipleJobs) {
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
                $tweetCreatedAt = Carbon::createFromTimestamp(strtotime($tweet->created_at ?? 1));

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
            ->map(function ($tweetsChunk) use (&$tweetsDividedForMultipleJobs) {
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
        $this->assertCount(30, Task::find(1)->likes->pluck('id_str'));
        $this->assertCount(4, Task::find(2)->likes);

        // Assert it's 6 Requests since we are requesting with parameter
        // count as '3' tweets while it's all 10 tweets.
        // So the first task 10 tweets will be fetched in 4 requests.
        // And the second task is parameterized to fetch only
        // within 4 tweets that will be fetched in 3 requests.
        $this->assertCount(
            13,
            TwitterClientMock::getAllCallsData(),
        );

        $initialMaxIdForSettingsTask = null;

        foreach (TwitterClientMock::getAllCallsData() as $index => $twitterCallData) {
            $this->assertEquals(
                $this->twitterEndpoint,
                $twitterCallData['endpoint'],
            );

            $expectedParameters = $this->initalTwitterParametersKeys;

            // 1st request and 11th are the first requests each was performed for a newly created task
            // So only them, aren't expected to have 'max_id' parameter

            if ($index !== 0 && $index < 11) {
                $expectedParameters = array_merge($this->initalTwitterParametersKeys, ['max_id']);
            } elseif ($index >= 11) {
                $expectedParameters = array_merge($this->initalTwitterParametersKeys, ['since_id', 'max_id']);
            }

            $this->assertEquals(
                $expectedParameters,
                array_keys($twitterCallData['parameters']),
                'Request ['.$index.'] to twitter doesn\'t contain the correct parameters.'
                .json_encode($twitterCallData)
            );

            if ($index == 11) {
                $initialMaxIdForSettingsTask = $twitterCallData['parameters']['max_id'];
            }

            if ($index > 11) {
                $this->assertNotNull($initialMaxIdForSettingsTask);
                $this->assertNotEquals($initialMaxIdForSettingsTask, $twitterCallData['parameters']['max_id']);
            }
        }
    }

    protected function assertZippedExists($taskId, $files)
    {
        $zippedFilesList = $this->getZippedFiles($taskId);

        foreach ((array) $files as $file) {
            $this->assertContains($taskId.'/media/'.$file, $zippedFilesList);
        }
    }

    protected function assertZippedMissing($taskId, $files)
    {
        $zippedFilesList = $this->getZippedFiles($taskId);
        foreach ((array) $files as $file) {
            $this->assertFalse(in_array($taskId.'/'.$file, $zippedFilesList));
        }
    }

    protected function getZippedFiles($taskId)
    {
        $disk = \Storage::disk(config('filesystems.cloud'));
        $path = $disk->path('').'/';
        $zippedPath = $disk->files($taskId)[0];

        $zipFile = new \PhpZip\ZipFile();
        $zipFile->openFile($path.$zippedPath);

        $result = collect($zipFile->getListFiles())
      ->map(
          function ($file) {
              if (substr($file, 0, 1) == '\\') {
                  return substr($file, 1);
              }

              return $file;
          }
      )
      ->map(
          function ($file) use ($taskId) {
              return $taskId.'/'.$file;
          }
      )
      ->toArray();

        $zipFile->close();

        return $result;
    }
}
