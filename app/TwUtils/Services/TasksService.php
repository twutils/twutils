<?php

namespace App\TwUtils\Services;

use App\Models\Task;
use App\Models\User;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\DestroyLikesOperation;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperationByUpload;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperationByUpload;
use App\TwUtils\UserManager;

class TasksService
{
    public const AVAILABLE_OPERATIONS = [
        FetchLikesOperation::class,
        FetchEntitiesLikesOperation::class,
        FetchUserTweetsOperation::class,
        FetchEntitiesUserTweetsOperation::class,
        FetchFollowingOperation::class,
        FetchFollowersOperation::class,
        DestroyLikesOperation::class,
        ManagedDestroyLikesOperation::class,
        ManagedDestroyTweetsOperation::class,
        DestroyTweetsOperation::class,
    ];

    public const AVAILABLE_UPLOADS_OPERATIONS = [
        ManagedDestroyLikesOperationByUpload::class,
        ManagedDestroyTweetsOperationByUpload::class,
    ];

    public function create(string $operationClassName, array $settings, Task $relatedTask = null, User $user, $managedByTaskId = null): Task
    {
        $socialUser = app(UserManager::class)->resolveUser($user, (new $operationClassName)->getScope());

        return Task::create(
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

    public function findOperationTypeByShortName($shortName, $withUpload = false)
    {
        return collect(
            $withUpload ? static::AVAILABLE_UPLOADS_OPERATIONS : static::AVAILABLE_OPERATIONS
        )->first(function ($operationClassName) use ($shortName) {
            return $shortName === (new $operationClassName)->getShortName();
        });
    }
}
