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
    protected $requestData;
    protected $relatedTask;
    protected $ok;
    protected $errors;
    protected $data;
    protected $statusCode;
    protected $managedByTaskId;

    public function __construct(string $targetedTask, array $requestData, Task $relatedTask = null, User $user)
    {
        $this->ok = false;
        $this->errors = [];
        $this->data = [];
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        $this->managedByTaskId = $requestData['managedByTaskId'] ?? null;

        $this->targetedTask = ucfirst($targetedTask);
        $this->requestData = $requestData;
        $this->relatedTask = $relatedTask;
        $this->user = $user;

        $operationClassName = collect(static::$availableTasks)->first(function ($operationClassName) {
            return $this->targetedTask == (new $operationClassName)->getShortName();
        });

        $socialUser = $this->resolveUser((new $operationClassName)->getScope());

        $this->addTask($socialUser, $operationClassName);
    }

    protected function hasValidManagedTaskId()
    {
        $lookupType = null;

        if (in_array($this->targetedTask, ['Likes', 'DestroyLikes'])) {
            $lookupType = ManagedDestroyLikesOperation::class;
        } elseif (in_array($this->targetedTask, ['UserTweets', 'DestroyTweets'])) {
            $lookupType = ManagedDestroyTweetsOperation::class;
        }

        $userManagedTasks = Task::where('status', 'queued')
        ->where('type', $lookupType)
        ->whereIn('socialuser_id', $this->user->socialUsers->pluck('id'))
        ->get()->last();

        return ! empty($userManagedTasks);
    }

    public function addTask(SocialUser $socialUser, $operationClassName)
    {
        $settings = ['targeted_task_id' => $this->relatedTask ? $this->relatedTask->id : null, 'settings' => $this->requestData['settings'] ?? null];

        if ($this->managedByTaskId && $this->hasValidManagedTaskId()) {
            $settings['managedByTaskId'] = $this->managedByTaskId;
        }

        $task = Task::create(
            [
                'socialuser_id'      => $socialUser->id,
                'type'               => $operationClassName,
                'status'             => 'queued',
                'extra'              => $settings,
                'managed_by_task_id' => $settings['managedByTaskId'] ?? null,
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
