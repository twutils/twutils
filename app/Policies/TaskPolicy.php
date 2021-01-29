<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the task.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Task $task
     *
     * @return mixed
     */
    public function view(User $user, Task $task)
    {
        return $user->socialUsers->pluck('id')->contains($task->socialuser_id);
    }

    /**
     * Determine whether the user can create tasks.
     *
     * @param \App\Models\User $user
     *
     * @return mixed
     */
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

    /**
     * Determine whether the user can update the task.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Task $task
     *
     * @return mixed
     */
    public function seeManagedTasks(User $user, Task $task)
    {
        return $this->view($user, $task);
    }

    /**
     * Determine whether the user can delete the task.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Task $task
     *
     * @return mixed
     */
    public function delete(User $user, Task $task)
    {
        return $task->managed_by_task_id === null && $this->view($user, $task);
    }
}
