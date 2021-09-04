<?php

namespace Tests\Feature\TwitterOperations;

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

    public function test_user_can_upload_task()
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
}
