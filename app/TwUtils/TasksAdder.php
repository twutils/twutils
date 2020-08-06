<?php

namespace App\TwUtils;

use App\Task;
use App\User;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\destroyLikesOperation;
use App\TwUtils\TwitterOperations\destroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;

class TasksAdder
{
    public static $availableTasks = [
        FetchLikesOperation::class,
        FetchEntitiesLikesOperation::class,
        FetchUserTweetsOperation::class,
        FetchEntitiesUserTweetsOperation::class,
        FetchFollowingOperation::class,
        FetchFollowersOperation::class,
        destroyLikesOperation::class,
        ManagedDestroyLikesOperation::class,
        ManagedDestroyTweetsOperation::class,
        destroyTweetsOperation::class,
    ];
    protected $user;
    protected $task;

    public function __construct(string $operationClassName, array $settings, Task $relatedTask = null, User $user, $managedByTaskId = null)
    {
        $this->user = $user;

        $socialUser = $this->resolveUser((new $operationClassName)->getScope());

        $this->task = Task::create(
            [
                'targeted_task_id'   => $relatedTask ? $relatedTask->id : null,
                'socialuser_id'      => $socialUser->id,
                'type'               => $operationClassName,
                'status'             => 'queued',
                'extra'              => ['settings' => $settings],
                'managed_by_task_id' => $managedByTaskId,
            ]
        );
    }

    public function resolveUser($taskScope)
    {
        return UserManager::resolveUser($this->user, $taskScope);
    }

    public function getTask() : Task
    {
        return $this->task;
    }

    public static function getAvailableTasks()
    {
        return array_keys(static::$availableTasks);
    }
}
