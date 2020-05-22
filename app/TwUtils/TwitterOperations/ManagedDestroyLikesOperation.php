<?php

namespace App\TwUtils\TwitterOperations;

use App\Jobs\CompleteManagedDestroyLikesJob;
use App\Jobs\CompleteTaskJob;
use App\SocialUser;
use App\Task;
use App\TwUtils\TasksAdder;

class ManagedDestroyLikesOperation extends TwitterOperation
{
    protected $scope = 'write';
    protected $tasksQueue = [FetchLikesOperation::class, destroyLikesOperation::class];
    protected $firstTaskShortName = 'Likes';
    protected $secondTaskShortName = 'DestroyLikes';

    public function dispatch()
    {
        $taskAdd = new TasksAdder($this->firstTaskShortName, ['managedByTaskId' => $this->task->id], $this->task, $this->socialUser->user);

        if ($taskAdd->isOk()) {
            $managedTask = Task::find($taskAdd->getData()['task_id']);

            return dispatch(new CompleteManagedDestroyLikesJob($managedTask, $this->socialUser, $this->task));
        }
    }

    protected function attachDestroyTweets(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        $taskAdd = new TasksAdder($this->secondTaskShortName, ['managedByTaskId' => $task->id, 'settings' => $task->extra['settings']], $managedTask, $socialUser->user);

        if ($taskAdd->isOk()) {
            $managedTask = Task::find($taskAdd->getData()['task_id']);

            dispatch(new CompleteManagedDestroyLikesJob($managedTask, $socialUser, $task));
        }
    }

    protected function markTaskAsCompleted(Task $task)
    {
        dispatch(new CompleteTaskJob($task));
    }

    // $managedTask->type = FetchLikesOperation::class
    protected function step1(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        if ($managedTask->status === 'completed') {
            $this->attachDestroyTweets($managedTask, $socialUser, $task);
        } elseif ($managedTask->status === 'queued') {
            dispatch(new CompleteManagedDestroyLikesJob($managedTask, $socialUser, $task));
        } elseif ($managedTask->status === 'broken') {
            $this->breakTask($task, $managedTask->extra['break_response'] ?? []);
        }
    }

    // $managedTask->type = destroyLikesOperation::class
    protected function step2(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        if ($managedTask->status === 'completed') {
            $this->markTaskAsCompleted($task);
        } elseif ($managedTask->status === 'queued') {
            dispatch(new CompleteManagedDestroyLikesJob($managedTask, $socialUser, $task));
        } elseif ($managedTask->status === 'broken') {
            $this->breakTask($task, $managedTask->extra['break_response'] ?? []);
        }
    }

    public function complete(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        $step = array_search($managedTask->type, $this->tasksQueue) + 1;

        call_user_func_array([$this, 'step'.$step], func_get_args());
    }
}
