<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Task $task)
    {
        return $user->socialUsers->pluck('id')->contains($task->socialuser_id);
    }

    public function create(User $user)
    {
        return config('twutils.tasks_limit_per_user')
            >
            Task::whereIn(
                'socialuser_id',
                $user->socialUsers->pluck('id')->toArray()
            )
            ->where('managed_by_task_id', null)
            ->count();
    }

    public function seeManagedTasks(User $user, Task $task)
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task)
    {
        return $task->managed_by_task_id === null && $this->view($user, $task);
    }
}
