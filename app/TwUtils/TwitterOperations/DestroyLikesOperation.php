<?php

namespace App\TwUtils\TwitterOperations;

use Carbon\Carbon;
use AppNext\Tasks\Config;
use App\TwUtils\Tasks\Validators\DateValidator;
use App\TwUtils\Tasks\Validators\ManagedByTaskValidator;

class DestroyLikesOperation extends TwitterOperation
{
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
        $targetedTask = $this->task->targetedTask;

        if ($targetedTask) {
            $targetedTask->getTweetsRelation()->updateExistingPivot($likeInstance->id_str, ['removed' => now()->format('Y-m-d H:i:s'), 'removed_task_id' => $this->task->id]);
        }
    }

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];
        $nextTweetIndex = $this->data['nextTweetIndex'];

        dispatch(new (Config::getJob($this::class))($this->socialUser, $nextTweetIndex, $this->likesCollection, $this->task))->delay($nextJobDelay);
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
        if (! $this->task->targetedTask) {
            return $this->breakTask($this->task, [], new \Exception("Target task doesn't exist"));
        }
        try {
            $relatedTaskLikes = $this->task->targetedTask->likes;

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

            return dispatch(new (Config::getJob($this::class))($this->socialUser, 0, $likes, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }

    public function getValidators(): array
    {
        return [DateValidator::class, ManagedByTaskValidator::class];
    }
}
