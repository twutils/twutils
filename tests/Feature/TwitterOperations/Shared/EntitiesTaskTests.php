<?php

namespace Tests\Feature\TwitterOperations\Shared;

use App\Jobs\CleanLikesJob;
use App\Jobs\FetchEntitiesUserTweetsJob;
use App\Jobs\ZipEntitiesJob;
use App\SocialUser;
use App\Task;
use App\Tweet;
use App\User;
use Config;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\IntegrationTestCase;

/*
 * A Generic abstract tests for all tasks that store and attach entities and
 * deal with files.
 */
abstract class EntitiesTaskTests extends IntegrationTestCase
{
    protected $jobName;
    protected $apiEndpoint;

    public function setUp() : void
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

        $this->fireJobsAndBindTwitter(
            [
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
            ],
            ]
        );

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

        $this->fireJobsAndBindTwitter(
            [
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
            ],
            ]
        );

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

        $this->fireJobsAndBindTwitter(
            [
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
            ],
            ]
        );

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_1.mp4');
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

        $this->fireJobsAndBindTwitter(
            [
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
            ],
            ]
        );

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedExists('1', $tweet->id_str.'_1.jpeg');
        $this->assertZippedExists('1', $tweet->id_str.'_1.mp4');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][0], $tweet->id_str.'_1.jpeg');
        $this->assertEquals(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0][1], $tweet->id_str.'_1.mp4');

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

        $this->fireJobsAndBindTwitter(
            [
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
            ],
            ]
        );

        $this->assertTaskCount(1, 'completed');
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedMissing('1', $tweet->id_str.'_1.zip');
        $this->assertTrue(empty(Task::all()->last()->tweets->first()->pivot->attachments['paths'][0]));
    }

    public function test_mixed_types_of_tweets()
    {
        $expectedSavedPaths = '1/media/10_1.jpeg,1/media/11_1.jpeg,1/media/12_1.jpeg,1/media/13_1.jpeg,1/media/14_1.jpeg,1/media/15_1.jpeg,1/media/16_1.jpeg,1/media/17_1.jpeg,1/media/18_1.jpeg,1/media/19_1.jpeg,1/media/20_1.jpeg,1/media/20_2.jpeg,1/media/21_1.jpeg,1/media/21_2.jpeg,1/media/22_1.jpeg,1/media/22_2.jpeg,1/media/23_1.jpeg,1/media/23_2.jpeg,1/media/24_1.jpeg,1/media/24_2.jpeg,1/media/25_1.jpeg,1/media/25_2.jpeg,1/media/26_1.jpeg,1/media/26_2.jpeg,1/media/27_1.jpeg,1/media/27_2.jpeg,1/media/28_1.jpeg,1/media/28_2.jpeg,1/media/29_1.jpeg,1/media/29_2.jpeg,1/media/30_1.jpeg,1/media/30_1.mp4,1/media/31_1.jpeg,1/media/31_1.mp4,1/media/32_1.jpeg,1/media/32_1.mp4,1/media/33_1.jpeg,1/media/33_1.mp4';

        $expectedTweetsAttachmentsPaths = '10_1.jpeg,11_1.jpeg,12_1.jpeg,13_1.jpeg,14_1.jpeg,15_1.jpeg,16_1.jpeg,17_1.jpeg,18_1.jpeg,19_1.jpeg,20_1.jpeg,20_2.jpeg,21_1.jpeg,21_2.jpeg,22_1.jpeg,22_2.jpeg,23_1.jpeg,23_2.jpeg,24_1.jpeg,24_2.jpeg,25_1.jpeg,25_2.jpeg,26_1.jpeg,26_2.jpeg,27_1.jpeg,27_2.jpeg,28_1.jpeg,28_2.jpeg,29_1.jpeg,29_2.jpeg,30_1.jpeg,30_1.mp4,31_1.jpeg,31_1.mp4,32_1.jpeg,32_1.mp4,33_1.jpeg,33_1.mp4';

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
                'type' => $this->jobName,
                'twitterData' => $this->generateUniqueTweets(10),
            ],
            [
                'type' => $this->jobName,
                'twitterData' => $this->generateUniqueTweets(10, $tweetWithOnePhoto),
            ],
            [
                'type' => $this->jobName,
                'twitterData' => $this->generateUniqueTweets(10, $tweetWithTwoPhotos),
            ],
            [
                'type' => $this->jobName,
                'twitterData' => $this->uniqueTweetIds([$tweetWithGif, $tweetWithVideo, $tweetWithGif, $tweetWithVideo]),
            ],
            [
                'type' => CleanLikesJob::class,
                'skip' => true,
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
    }

    public function test_do_nothing_with_regualr_tweets()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $tweet = $this->getStub('tweet.json');

        $this->bindTwitterConnector([$tweet]);

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
        $this->assertEquals(Tweet::all()->count(), 1);
        $this->assertLikesBelongsToTask();
        $this->assertZippedMissing('1', $tweet->id_str.'_1.jpeg');
        $this->assertTrue(empty(Task::all()->last()->tweets->first()->pivot->attachments));
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
