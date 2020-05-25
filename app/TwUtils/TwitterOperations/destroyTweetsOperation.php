<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use Carbon\Carbon;
use App\Jobs\DestroyTweetJob;

class destroyTweetsOperation extends destroyLikesOperation
{
    protected $endpoint = 'statuses/destroy';
    protected $scope = 'write';
    protected $httpMethod = 'post';
    protected $likesCollection = [];

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];
        $nextTweetIndex = $this->data['nextTweetIndex'];

        dispatch(new DestroyTweetJob($this->socialUser, $nextTweetIndex, $this->likesCollection, $this->task))->delay($nextJobDelay);
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

            return dispatch(new DestroyTweetJob($this->socialUser, 0, $likes, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}
