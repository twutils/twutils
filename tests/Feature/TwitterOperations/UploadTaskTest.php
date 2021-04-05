<?php

namespace Tests\Feature\TwitterOperations;

use App\Models\Upload;
use App\Models\RawTweet;
use Tests\IntegrationTestCase;
use Illuminate\Http\UploadedFile;

class UploadTaskTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_upload_task()
    {
        $this->withoutJobs();
        $this->logInSocialUser('api');

        $this->postJson('api/tasks/upload', [
            'purpose' => 'remove_tweets',
            'file'    => UploadedFile::fake()->createWithContent('tweet.js', $this->getRawStub('twitter_archive_data_tweet.js')),
        ])
        ->assertSuccessful();

        $this->fireJobsAndBindTwitter();

        $this->assertCount(1, Upload::all());
        $this->assertCount(5, RawTweet::all());

        $this->assertCount(5, Upload::find(1)->rawTweets);
        $this->assertSame(1, RawTweet::first()->upload->id);
    }
}
