<?php

namespace App\TwUtils\TwitterOperations;

use App\Task;
use App\Following;
use App\TwUtils\TweepsManager;
use App\Jobs\FetchFollowingJob;
use App\Jobs\CleanFollowingsJob;
use App\Jobs\FetchFollowingLookupsJob;

class FetchFollowingOperation extends TwitterOperation
{
    protected $endpoint = 'friends/list';
    protected $scope = 'read';
    protected $httpMethod = 'get';

    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $parameters = $this->buildParameters();
        $parameters['cursor'] = $this->response['next_cursor_str'];

        dispatch(new FetchFollowingJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    protected function shouldBuildNextJob()
    {
        $this->task = $this->task->fresh();

        if ($this->task->status != 'queued' && $this->task->status != 'staging') {
            return false;
        }

        $shouldBuild = $this->response['next_cursor_str'] != '0';

        if (! $shouldBuild) {
            $this->setCompletedTask($this->task);
        }

        return $shouldBuild;
    }

    protected function setCompletedTask($task)
    {
        $task->status = 'staging';
        $task->save();
        $this->afterCompletedTask($task);
    }

    protected function afterCompletedTask(Task $task)
    {
        dispatch(new FetchFollowingLookupsJob(['index' => 0], $this->socialUser, $this->task));
    }

    protected function saveResponse()
    {
        $users = collect($this->response['users']);
        $task = $this->task;

        if ($users->count() === 0) {
            return;
        }

        $followings = [];
        $users
        ->each(
            function ($user) use (&$followings, $task) {
                $user = (array) $user;

                $tweep = TweepsManager::createOrFindFromFollowing($user);

                array_push(
                    $followings,
                    [
                        'task_id'      => $task->id,
                        'tweep_id_str' => $tweep->id_str,
                    ]
                );
            }
        );

        if (app('env') === 'testing' && app()->has('BeforeFollowingInsertHook')) {
            app()->make('BeforeFollowingInsertHook');
        }

        if (! $this->shouldContinueProcessing()) {
            return;
        }

        foreach (collect($followings)->chunk(50) as $i => $followingsGroup) {
            Following::insert($followingsGroup->toArray());
        }

        dispatch(new CleanFollowingsJob($this->task));
    }

    protected function buildParameters()
    {
        return [
            'user_id' => $this->socialUser->social_user_id,
            'count'   => 200,
            'cursor'  => -1,
        ];
    }

    public function dispatch()
    {
        $this->parameters = $this->buildParameters();

        try {
            return dispatch(new FetchFollowingJob($this->parameters, $this->socialUser, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}
