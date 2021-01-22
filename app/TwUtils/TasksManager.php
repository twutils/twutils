<?php

namespace App\TwUtils;

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

class TasksManager
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

    public static function findOperationTypeByShortName($shortName)
    {
        return collect(static::AVAILABLE_OPERATIONS)->first(function ($operationClassName) use ($shortName) {
            return $shortName === (new $operationClassName)->getShortName();
        });
    }
}
