<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\RawTweet;
use App\TwUtils\Base\Job;
use Illuminate\Support\Arr;
use App\TwUtils\Services\RawTweetsService;

class ProcessUploadJob extends Job
{
    protected RawTweetsService $rawTweetsManager;

    public function __construct(
        protected Upload $upload
    ) {
        $this->rawTweetsManager = app(RawTweetsService::class);
    }

    public function handle()
    {
        $uploadedFile = Upload::getStorageDisk()->get(Upload::UPLOADS_DIR.'/'.$this->upload->filename);

        $tweets = collect(preg_split('#window.(.*?) = #', $uploadedFile))
            ->map(fn ($part) => (empty(trim($part)) ? false : (json_decode($part) ?: false)))
            ->filter()
            ->flatten()
            ->map
            ->tweet
            ->map(fn ($tweet)    => json_decode(json_encode($tweet), true))
            ->filter(fn ($tweet) => isset($tweet['id_str']) && ! preg_match('/[^0-9]/', $tweet['id_str']))
            ->map(fn ($tweet)    => $this->rawTweetsManager->mapResponseToTweet($tweet))
            ->map(fn ($tweet)    => Arr::set($tweet, 'extended_entities', json_encode($tweet['extended_entities'])))
            ->map(fn ($tweet)    => Arr::set($tweet, 'upload_id', $this->upload->id))
            ->values()
            ->toArray();

        RawTweet::insert($tweets);
    }
}
