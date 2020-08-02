<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use App\Media;
use App\Tweet;
use App\TwUtils\AssetsManager;
use App\Jobs\FetchEntitiesLikesJob;

class FetchEntitiesLikesOperation extends FetchLikesOperation
{
    protected $shortName = 'EntitiesLikes';
    protected $downloadTweetsWithMedia = true;

    protected function shouldBuildNextJob()
    {
        $response = collect($this->response);

        $this->task = $this->task->fresh();

        if ($this->task->status != 'queued') {
            return false;
        }

        $shouldBuild = $response->count() >= config('twutils.minimum_expected_likes');

        if (! $shouldBuild) {
            $tweetsWithMedia = $this->task->tweets
                ->filter(fn (Tweet $tweet) => AssetsManager::hasMedia($tweet))
                ->values();

            $totalTweets = $tweetsWithMedia->count();

            $tweetsWithMedia->map(function ($tweet) {
                $tweet->media->map(function (Media $media) {
                    $media->status = Media::STATUS_STARTED;
                    $media->save();
                });
            });

            $this->setCompletedTask($this->task);
        }

        return $shouldBuild;
    }

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $last = (array) collect($this->response)->last();

        $parameters = $this->buildParameters();

        $parameters['max_id'] = $last['id_str'];

        dispatch(new FetchEntitiesLikesJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    public function initJob()
    {
        $disks = [];

        foreach ($disks as $disk) {
            if (! $disk->exists($this->task->id)) {
                $disk->makeDirectory($this->task->id);
            }
        }
    }

    protected function afterCompletedTask(Task $task)
    {
        parent::afterCompletedTask($task);
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        try {
            return dispatch(new FetchEntitiesLikesJob($parameters, $this->socialUser, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}
