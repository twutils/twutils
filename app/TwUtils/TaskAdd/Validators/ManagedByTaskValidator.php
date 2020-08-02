<?php

namespace App\TwUtils\TaskAdd\Validators;

use App\Task;
use App\Exceptions\TaskAddException;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation;
use App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation;

class ManagedByTaskValidator
{
    public function apply($requestData, $user)
    {
        if (empty($requestData['managedByTaskId']))
        {
            return ;
        }

        $lookupType = null;

        if (in_array($requestData['targetedTask'], ['Likes', 'DestroyLikes'])) {
            $lookupType = ManagedDestroyLikesOperation::class;
        } elseif (in_array($requestData['targetedTask'], ['UserTweets', 'DestroyTweets'])) {
            $lookupType = ManagedDestroyTweetsOperation::class;
        }

        $userManagedTasks = Task::where('status', 'queued')
        ->where('id', $requestData['managedByTaskId'])
        ->where('type', $lookupType)
        ->whereIn('socialuser_id', $user->socialUsers->pluck('id'))
        ->get()->last();

        if (empty($userManagedTasks))
        {
            throw new TaskAddException(["Invalid managed-by-task value."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
   }
}