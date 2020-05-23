<?php

namespace App\Policies;

use App\Task;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the task.
     *
     * @param \App\User $user
     * @param \App\Task $task
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
     * @param \App\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the task.
     *
     * @param \App\User $user
     * @param \App\Task $task
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
     * @param \App\User $user
     * @param \App\Task $task
     *
     * @return mixed
     */
    public function delete(User $user, Task $task)
    {
        return $task->managed_by_task_id === null && $this->view($user, $task);
    }
}
