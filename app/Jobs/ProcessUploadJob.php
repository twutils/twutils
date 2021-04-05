<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\RawTweet;
use App\TwUtils\Base\Job;
use Illuminate\Support\Arr;
use App\TwUtils\RawTweetsManager;

class ProcessUploadJob extends Job
{
    public function __construct(
        protected Upload $upload
    ) {
    }

    public function handle()
    {
        $uploadedFile = Upload::getStorageDisk()->get(Upload::UPLOADS_DIR.'/'.$this->upload->filename);

        $rawTweetsManager = new RawTweetsManager();

        $tweets = collect(preg_split('#window.(.*?) = #', $uploadedFile))
            ->map(fn ($part) => (empty(trim($part)) ? false : (json_decode($part) ?: false)))
            ->filter()
            ->flatten()
            ->map
            ->tweet
            ->map(fn ($tweet)    => json_decode(json_encode($tweet), true))
            ->filter(fn ($tweet) => isset($tweet['id_str']) && ! preg_match('/[^0-9]/', $tweet['id_str']))
            ->map(fn ($tweet)    => $rawTweetsManager->mapResponseToTweet($tweet))
            ->map(fn ($tweet)    => Arr::set($tweet, 'extended_entities', json_encode($tweet['extended_entities'])))
            ->map(fn ($tweet)    => Arr::set($tweet, 'upload_id', $this->upload->id))
            ->values()
            ->toArray();

        RawTweet::insert($tweets);
    }
}
