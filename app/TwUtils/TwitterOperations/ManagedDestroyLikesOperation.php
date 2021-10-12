<?php

namespace App\TwUtils\TwitterOperations;

use App\Models\Task;
use App\Models\SocialUser;
use App\Jobs\CompleteTaskJob;
use App\Jobs\CompleteManagedDestroyLikesJob;
use App\TwUtils\Services\TasksService;
use App\TwUtils\Tasks\Validators\DateValidator;

class ManagedDestroyLikesOperation extends TwitterOperation
{
    protected $shortName = 'ManagedDestroyLikes';

    protected $scope = 'write';

    protected $tasksQueue = [FetchLikesOperation::class, DestroyLikesOperation::class];

    public function dispatch()
    {
        $managedTask = app(TasksService::class)->create($this->tasksQueue[0], $this->task->extra['settings'] ?? [], $this->task, $this->socialUser->user, $this->task->id);

        return dispatch(new CompleteManagedDestroyLikesJob($managedTask, $this->socialUser, $this->task));
    }

    protected function attachDestroyTweets(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        $managedTask = app(TasksService::class)->create($this->tasksQueue[1], $task->extra['settings'] ?? [], $managedTask, $socialUser->user, $task->id);

        dispatch(new CompleteManagedDestroyLikesJob($managedTask, $socialUser, $task));
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
            dispatch(new CompleteManagedDestroyLikesJob($managedTask, $socialUser, $task))->delay(now()->addSeconds(10));
        } elseif ($managedTask->status === 'broken') {
            $this->breakTask($task, $managedTask->extra['break_response'] ?? []);
        }
    }

    // $managedTask->type = DestroyLikesOperation::class
    protected function step2(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        if ($managedTask->status === 'completed') {
            $this->markTaskAsCompleted($task);
        } elseif ($managedTask->status === 'queued') {
            dispatch(new CompleteManagedDestroyLikesJob($managedTask, $socialUser, $task))->delay(now()->addSeconds(10));
        } elseif ($managedTask->status === 'broken') {
            $this->breakTask($task, $managedTask->extra['break_response'] ?? []);
        }
    }

    public function complete(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        $step = array_search($managedTask->type, $this->tasksQueue) + 1;

        call_user_func_array([$this, 'step'.$step], func_get_args());
    }

    public function getValidators(): array
    {
        return [DateValidator::class];
    }
}
