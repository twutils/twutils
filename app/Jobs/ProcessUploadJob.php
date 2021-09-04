<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\RawTweet;
use App\TwUtils\Base\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\TwUtils\Services\RawTweetsService;

class ProcessUploadJob extends Job
{
    protected RawTweetsService $rawTweetsService;

    public function __construct(
        protected Upload $upload
    ) {
        $this->rawTweetsService = app(RawTweetsService::class);
    }

    public function handle()
    {
        $tweets = $this->getUploadTweets()
            ->values()
            ->toArray();

        RawTweet::insert($tweets);
    }

    protected function getUploadTweets(): Collection
    {
        $parts = $this->getUploadedFileParts();

        if ($this->upload->purpose === 'DestroyLikes') {
            return $this->parseLikes($parts);
        }

        return $this->parseTweets($parts);
    }

    protected function getUploadedFileParts(): Collection
    {
        $uploadedFile = Upload::getStorageDisk()->get(Upload::UPLOADS_DIR.'/'.$this->upload->filename);

        return collect(preg_split('#window.(.*?) = #', $uploadedFile))
            ->map(fn ($part) => (empty(trim($part)) ? false : (json_decode($part) ?: false)))
            ->filter()
            ->flatten();
    }

    protected function parseLikes(Collection $parts): Collection
    {
        return $parts
            ->map(function ($part) {
                return $part->like ?? null;
            })
            ->filter()

            ->map(fn ($like)    => json_decode(json_encode($like), true))
            ->filter(fn ($like) => isset($like['tweetId']) && ! preg_match('/[^0-9]/', $like['tweetId']))
            ->map(fn ($tweet)   => Arr::set($tweet, 'extended_entities', json_encode([])))
            ->map(fn ($like)    => $this->rawTweetsService->mapResponseToTweet($like))
            ->map(fn ($like)    => Arr::set($like, 'upload_id', $this->upload->id));
    }

    protected function parseTweets(Collection $parts): Collection
    {
        return $parts
            ->map(function ($part) {
                return $part->tweet ?? null;
            })
            ->filter()

            ->map(fn ($tweet)    => json_decode(json_encode($tweet), true))
            ->filter(fn ($tweet) => isset($tweet['id_str']) && ! preg_match('/[^0-9]/', $tweet['id_str']))

            ->map(fn ($tweet) => $this->rawTweetsService->mapResponseToTweet($tweet))
            ->map(fn ($tweet) => Arr::set($tweet, 'extended_entities', json_encode($tweet['extended_entities'])))
            ->map(fn ($tweet) => Arr::set($tweet, 'upload_id', $this->upload->id));
    }
}
