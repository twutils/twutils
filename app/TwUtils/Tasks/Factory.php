<?php

namespace App\TwUtils\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Models\SocialUser;
use App\TwUtils\UserManager;

class Factory
{
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

    public function resolveUser($taskScope): SocialUser
    {
        return app(UserManager::class)->resolveUser($this->user, $taskScope);
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
