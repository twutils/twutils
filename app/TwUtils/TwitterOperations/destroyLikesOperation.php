<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use Carbon\Carbon;
use App\Jobs\DislikeTweetJob;

class destroyLikesOperation extends TwitterOperation
{
    protected $endpoint = 'favorites/destroy';
    protected $scope = 'write';
    protected $httpMethod = 'post';
    protected $likesCollection = [];

    protected function handleJobParameters($parameters)
    {
        $index = $parameters['index'];
        $likesCollection = $parameters['likesCollection']->values();

        $this->likesCollection = $likesCollection;

        $likeInstance = $likesCollection[$index];
        $likeId = $likesCollection[$index]->id_str;

        $nextTweetIndex = $this->getNextTweetIndex($likesCollection, $index);

        $this->data['nextTweetIndex'] = $nextTweetIndex;
        $this->data['likeInstance'] = $likeInstance;
        $this->parameters = ['id' => $likeId];
    }

    protected function getNextTweetIndex($likesCollection, $index)
    {
        $currentIndexIsLastOne = $likesCollection->count() - 1 == $index;

        $nextTweetIndex = $currentIndexIsLastOne ? null : $index + 1;

        return $nextTweetIndex;
    }

    protected function handleErrorResponse()
    {
        $alreadyDestroyedTweets = false;
        foreach ($this->response['errors'] as $error) {
            $error = (array) $error;
            if (! empty($error['code']) && ! in_array($error['code'], [144])) {
                $this->breakTask($this->task, $this->response);
                break;
            } elseif (! empty($error['code']) && $error['code'] == 144) {
                $alreadyDestroyedTweets = true;
            }
        }

        if ($this->shouldBuildNextJob()) {
            $this->buildNextJob();
        }
    }

    protected function saveResponse()
    {
        $likeInstance = $this->data['likeInstance'];
        $targetedTask = Task::find($this->task->extra['targeted_task_id']);

        if ($targetedTask) {
            $targetedTask->tweets()->updateExistingPivot($likeInstance->id_str, ['removed' => now()->format('Y-m-d H:i:s'), 'removed_task_id' => $this->task->id]);
        }
    }

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];
        $nextTweetIndex = $this->data['nextTweetIndex'];

        dispatch(new DislikeTweetJob($this->socialUser, $nextTweetIndex, $this->likesCollection, $this->task))->delay($nextJobDelay);
    }

    protected function shouldBuildNextJob()
    {
        $this->task = $this->task->fresh();

        $nextTweetIndex = $this->data['nextTweetIndex'];

        if ($this->task->status != 'queued') {
            return false;
        }

        $shouldBuild = ! is_null($nextTweetIndex);

        if (! $shouldBuild) {
            $this->setCompletedTask($this->task);
        }

        return $shouldBuild;
    }

    protected function buildParameters()
    {
        return $this->data;
    }

    public function dispatch()
    {
        $parameters = $this->buildParameters();

        try {
            $relatedTaskLikes = Task::find($parameters['targeted_task_id'])->likes;

            if (! empty($parameters['settings'])) {
                if (isset($parameters['settings']['start_date'])) {
                    $relatedTaskLikes = $relatedTaskLikes->where('tweet_created_at', '>', new Carbon($parameters['settings']['start_date']));
                }

                if (isset($parameters['settings']['end_date'])) {
                    $relatedTaskLikes = $relatedTaskLikes->where('tweet_created_at', '<', new Carbon($parameters['settings']['end_date']));
                }
            }

            $likes = $relatedTaskLikes->values()->unique('id_str')->where('pivot.removed', null);

            $countLikes = $likes->count();

            $this->task->extra = array_merge($this->task->extra, ['removeScopeCount' => $countLikes]);
            $this->task->save();

            if ($countLikes === 0) {
                return $this->setCompletedTask($this->task);
            }

            return dispatch(new DislikeTweetJob($this->socialUser, 0, $likes, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}
