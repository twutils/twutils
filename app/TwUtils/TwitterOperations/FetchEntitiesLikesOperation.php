<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use App\Jobs\ZipEntitiesJob;
use App\TwUtils\AssetsManager;
use App\Jobs\SaveTweetMediaJob;
use App\Jobs\FetchEntitiesLikesJob;

class FetchEntitiesLikesOperation extends FetchLikesOperation
{
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
                ->filter(function ($tweet) {
                    return AssetsManager::hasMedia($tweet);
                })
                ->values();

            $totalTweets = $tweetsWithMedia->count();

            $tweetsWithMedia->map(function ($tweet, $index) use ($totalTweets) {
                dispatch(new SaveTweetMediaJob($tweet->id_str, $this->task, $index, $totalTweets));
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
        $disks = [\Storage::disk('temporaryTasks'), \Storage::disk('tasks')];

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
