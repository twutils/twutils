<?php

namespace Tests\Feature\TwitterOperations;

use App\Models\Task;
use App\Models\Upload;
use App\Models\RawTweet;
use Tests\IntegrationTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadTaskTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_upload_file()
    {
        Storage::fake();

        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->postJson('api/tasks/upload', [
            'purpose' => 'destroyTweets',
            'file'    => UploadedFile::fake()->createWithContent('tweet.js', $this->getRawStub('twitter_archive_data_tweet.js')),
        ])
        ->assertSuccessful();

        $this->fireJobsAndBindTwitter();

        $this->assertCount(1, Upload::all());
        $this->assertCount(5, Upload::find(1)->rawTweets);
        $this->assertCount(1, Upload::getStorageDisk()->allFiles(''));

        $this->deleteJson('api/tasks/uploads/1')->assertSuccessful();

        $this->assertCount(0, Upload::getStorageDisk()->allFiles(''));
        $this->assertCount(0, Upload::all());
        $this->assertCount(0, RawTweet::all());
    }

    public function test_user_can_upload_file_and_use_it_to_destroy_tweets()
    {
        Storage::fake();

        $this->withoutJobs();
        $this->logInSocialUserForDestroyTweets('api');

        $this->postJson('api/tasks/upload', [
            'purpose' => 'destroyTweets',
            'file'    => UploadedFile::fake()->createWithContent('tweet.js', $this->getRawStub('twitter_archive_data_tweet.js')),
        ])
        ->assertSuccessful();

        $this->fireJobsWithoutRepeat([]);

        $this->postJson(
            'api/ManagedDestroyTweets',
            [
                    'settings' => [
                        'retweets'     => false,
                        'tweets'       => false,
                        'replies'      => false,
                        'start_date'   => null,
                        'end_date'     => null,
                        'tweetsSource' => 'file',
                        'chosenUpload' => 1,
                    ],
            ]
        )
        ->dump()
        ->assertSuccessful();

        dd(Task::all(), 'tasks');
        $this->fireJobsWithoutRepeat([]);
    }
}
