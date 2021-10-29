<?php

namespace Tests\Feature\TwitterOperations\Shared;

use Config;
use App\Models\Task;
use App\Models\Media;
use App\Models\Tweet;
use App\Models\Export;
use App\Models\MediaFile;
use Tests\TwitterClientMock;
use Illuminate\Support\Carbon;
use Tests\IntegrationTestCase;
use App\Jobs\ProcessMediaFileJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

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
        $this->enableExportsQueue();
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

    public function test_one_tweet_contains_one_photo()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_one_photo.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertEquals(Task::all()->last()->tweets->first()->media->map->mediaFiles[0][0]->mediaPath, $tweet->id_str.'_1.jpeg');
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

        $response = $this->getJson(route('tasks.getTaskData', ['task' => Task::all()->last()->id]));

        $this->assertEquals('success', $response->json()['data'][0]['media'][0]['media_files'][0]['status']);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_2.jpeg');
        $this->assertEquals($tweet->id_str.'_1.jpeg', $response->json()['data'][0]['media'][0]['media_files'][0]['mediaPath']);
        $this->assertEquals($tweet->id_str.'_2.jpeg', $response->json()['data'][0]['media'][1]['media_files'][0]['mediaPath']);
    }

    public function test_basic_save_two_photos_database_relations()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet_with_two_photos.json');

        $this->bindTwitterConnector([$tweet]);

        $this->getJson($this->apiEndpoint)
        ->assertStatus(200);

        $this->fireJobsAndBindTwitter([]);

        $lastJobIndex = count($this->dispatchedJobs);

        $this->assertLikesBelongsToTask();
        $this->assertCount(2, Tweet::first()->media);
        $this->assertCount(2, Media::all());
        $this->assertCount(2, MediaFile::all());
        $this->assertCount(1, Media::find(1)->mediaFiles);
        $this->assertCount(1, Media::find(2)->mediaFiles);
        $this->assertCount(3, Task::first()->exports);
        $this->assertEquals('success', Export::first()->status);
        $this->assertEquals('success', Media::first()->status);
        $this->assertCount(2, Storage::disk(config('filesystems.tweetsMedia'))->allFiles(''));
        $this->assertCount(3, Storage::disk(config('filesystems.cloud'))->allFiles(''));
        $this->assertTaskCount(1, 'completed');

        $this->assertZippedExists('1', [$tweet->id_str.'_1.jpeg', $tweet->id_str.'_2.jpeg']);

        $this->deleteJson('api/tasks/1');

        $this->fireJobsAndBindTwitter([], $lastJobIndex);

        $this->assertCount(0, Tweet::all());
        $this->assertCount(0, Media::all());
        $this->assertCount(0, MediaFile::all());
        $this->assertCount(0, Task::all());
        $this->assertCount(0, Export::all());
        $this->assertCount(0, Storage::disk(config('filesystems.tweetsMedia'))->allFiles(''));
        $this->assertCount(0, Storage::disk(config('filesystems.cloud'))->allFiles(''));
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

        $response = $this->getJson(route('tasks.getTaskData', ['task' => Task::all()->last()->id]));

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_2.mp4');
        $this->assertEquals($tweet->id_str.'_1.jpeg', $response->json()['data'][0]['media'][0]['media_files'][0]['mediaPath']);
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

        $response = $this->getJson(route('tasks.getTaskData', ['task' => Task::all()->last()->id]));

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_2.mp4');
        $this->assertEquals($tweet->id_str.'_1.jpeg', $response->json()['data'][0]['media'][0]['media_files'][0]['mediaPath']);
        $this->assertEquals($tweet->id_str.'_2.mp4', $response->json()['data'][0]['media'][0]['media_files'][1]['mediaPath']);

        $response = $this->get('task/1/export/1');
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

        $response = $this->getJson(route('tasks.getTaskData', ['task' => Task::all()->last()->id]));

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedMissing('1', $tweet->id_str.'_1.zip');
        $this->assertEquals('broken', $response->json()['data'][0]['media'][0]['media_files'][0]['status']);
    }

    public function test_mixed_types_of_tweets()
    {
        $expectedSavedPaths = 'media/11_2.jpeg,media/12_3.jpeg,media/13_4.jpeg,media/14_5.jpeg,media/15_6.jpeg,media/16_7.jpeg,media/17_8.jpeg,media/18_9.jpeg,media/19_10.jpeg,media/20_11.jpeg,media/20_12.jpeg,media/21_13.jpeg,media/21_14.jpeg,media/22_15.jpeg,media/22_16.jpeg,media/23_17.jpeg,media/23_18.jpeg,media/24_19.jpeg,media/24_20.jpeg,media/25_21.jpeg,media/25_22.jpeg,media/26_23.jpeg,media/26_24.jpeg,media/27_25.jpeg,media/27_26.jpeg,media/28_27.jpeg,media/28_28.jpeg,media/29_29.jpeg,media/29_30.jpeg,media/30_31.jpeg,media/30_32.mp4,media/31_33.jpeg,media/31_34.mp4,media/32_35.jpeg,media/32_36.mp4,media/33_37.jpeg,media/33_38.mp4';

        $expectedTweetsAttachmentsPaths = '11_2.jpeg,12_3.jpeg,13_4.jpeg,14_5.jpeg,15_6.jpeg,16_7.jpeg,17_8.jpeg,18_9.jpeg,19_10.jpeg,20_11.jpeg,20_12.jpeg,21_13.jpeg,21_14.jpeg,22_15.jpeg,22_16.jpeg,23_17.jpeg,23_18.jpeg,24_19.jpeg,24_20.jpeg,25_21.jpeg,25_22.jpeg,26_23.jpeg,26_24.jpeg,27_25.jpeg,27_26.jpeg,28_27.jpeg,28_28.jpeg,29_29.jpeg,29_30.jpeg,30_31.jpeg,30_32.mp4,31_33.jpeg,31_34.mp4,32_35.jpeg,32_36.mp4,33_37.jpeg,33_38.mp4';

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
                    'type'        => ProcessMediaFileJob::class,
                    'twitterData' => [],
                    'before'      => function () {
                        app('HttpClient')->throwException(1);
                    },
                ],
                [
                    'type'        => ProcessMediaFileJob::class,
                    'twitterData' => [],
                    'before'      => function () {
                    },
                ],
            ]
        );

        $response = $this->getJson(route('tasks.getTaskData', ['task' => Task::all()->last()->id]));

        $likeEntitiesPaths = '';
        collect($response->json()['data'])
        ->map(
            function ($tweet) use (&$likeEntitiesPaths) {
                foreach ($tweet['media'] as $media) {
                    foreach ($media['media_files'] as $mediaFile) {
                        if ($mediaFile['status'] === MediaFile::STATUS_SUCCESS) {
                            $likeEntitiesPaths .= $mediaFile['mediaPath'].',';
                        }
                    }
                }
            }
        );

        $likeEntitiesPaths = substr($likeEntitiesPaths, 0, -1);

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 34);
        $this->assertLikesBelongsToTask();
        $this->assertStringContainsString($expectedTweetsAttachmentsPaths, $likeEntitiesPaths);
        $this->assertStringContainsString($expectedSavedPaths, collect($this->getZippedFiles(3))->implode(','));
        $this->assertEquals(['success', 'success', 'success'], Task::find(1)->exports->pluck('status')->toArray());
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
        $this->assertCount(0, MediaFile::all());
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
                $twitterCallData['path'],
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
        $exportId = Task::find($taskId)->exports->where('type', Export::TYPE_HTMLENTITIES)->first()->id;

        $zippedFilesList = $this->getZippedFiles($exportId);

        foreach ((array) $files as $file) {
            $this->assertContains('media/'.$file, $zippedFilesList);
        }
    }

    protected function assertZippedMissing($taskId, $files)
    {
        $zippedFilesList = $this->getZippedFiles($taskId);
        foreach ((array) $files as $file) {
            $this->assertFalse(in_array($taskId.'/'.$file, $zippedFilesList));
        }
    }

    protected function getZippedFiles($exportId)
    {
        $disk = \Storage::disk(config('filesystems.cloud'));
        $path = $disk->path($exportId);

        $zipFile = new \PhpZip\ZipFile();
        $zipFile->openFile($path);

        $result = $zipFile->getListFiles();

        $zipFile->close();

        return $result;
    }
}
