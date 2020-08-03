<?php

namespace App\TwUtils;

use App\SocialUser;
use App\Task;
use App\User;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\destroyLikesOperation;
use App\TwUtils\TwitterOperations\destroyTweetsOperation;

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
    protected $socialUser;

    protected $targetedTask;
    protected $settings;
    protected $relatedTask;
    protected $ok;
    protected $errors;
    protected $data;
    protected $statusCode;
    protected $managedByTaskId;

    public function __construct(string $targetedTask, array $settings, Task $relatedTask = null, User $user, $managedByTaskId = null)
    {
        $this->ok = false;
        $this->errors = [];
        $this->data = [];
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        $this->managedByTaskId = $managedByTaskId;

        $this->targetedTask = $targetedTask;
        $this->settings = $settings;
        $this->relatedTask = $relatedTask;
        $this->user = $user;

        $operationClassName = $targetedTask;

        $socialUser = $this->resolveUser((new $operationClassName)->getScope());

        $settings = ['settings' => $this->settings];

        $task = Task::create(
            [
                'targeted_task_id'   => $this->relatedTask ? $this->relatedTask->id : null,
                'socialuser_id'      => $socialUser->id,
                'type'               => $operationClassName,
                'status'             => 'queued',
                'extra'              => $settings,
                'managed_by_task_id' => $managedByTaskId,
            ]
        );

        $this->ok = true;
        $this->statusCode = Response::HTTP_OK;
        $this->errors = [];
        $this->data = array_merge($this->data, ['task_id' => $task->id]);
    }


    public function resolveUser($taskScope)
    {
        return UserManager::resolveUser($this->user, $taskScope);
    }

    public function isOk()
    {
        return $this->ok;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public static function getAvailableTasks()
    {
        return array_keys(static::$availableTasks);
    }
}
