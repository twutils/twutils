<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;
use App\Models\Follower;
use App\Jobs\CompleteTaskJob;
use App\Jobs\CleanFollowersJob;
use App\Jobs\FetchFollowersJob;
use App\TwUtils\Services\TweepsService;

class FetchFollowersOperation extends FetchFollowingOperation
{
    protected function buildNextJob()
    {
        $nextJobDelay = $this->data['nextJobDelay'];

        $parameters = $this->buildParameters();
        $parameters['cursor'] = $this->response['next_cursor_str'];

        dispatch(new FetchFollowersJob($parameters, $this->socialUser, $this->task))->delay($nextJobDelay);
    }

    protected function shouldBuildNextJob()
    {
        $this->task = $this->task->fresh();

        if ($this->task->status != 'queued') {
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
        dispatch(new CompleteTaskJob($task));
        $this->afterCompletedTask($task);
    }

    protected function afterCompletedTask(Task $task)
    {
    }

    protected function saveResponse()
    {
        $users = collect($this->response['users']);
        $task = $this->task;

        if ($users->count() === 0) {
            return;
        }

        $followers = [];
        $users
        ->each(
            function ($user) use (&$followers, $task) {
                $user = (array) $user;

                $tweep = app(TweepsService::class)->createOrFindFromFollowing($user);

                array_push(
                    $followers,
                    [
                        'task_id'        => $task->id,
                        'tweep_id_str'   => $tweep->id_str,
                        'followed_by_me' => $user['following'] ?? false,
                    ]
                );
            }
        );

        if (app('env') === 'testing' && app()->has('BeforeFollowersInsertHook')) {
            app()->make('BeforeFollowersInsertHook');
        }

        if (! $this->shouldContinueProcessing()) {
            return;
        }

        foreach (collect($followers)->chunk(50) as $i => $followersGroup) {
            Follower::insert($followersGroup->toArray());
        }

        dispatch(new CleanFollowersJob($this->task));
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
            return dispatch(new FetchFollowersJob($this->parameters, $this->socialUser, $this->task));
        } catch (\Exception $e) {
            if (app('env') === 'testing') {
                dd($e);
            }
        }
    }
}
