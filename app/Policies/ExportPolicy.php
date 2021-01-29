<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Models\Export;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExportPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Export $export, Task $task)
    {
        if ($user->cannot('view', $task)) {
            return false;
        }

        return $export->task_id === $task->id;
    }

    public function delete(User $user, Export $export)
    {
        return $user->can('view', [$export, $export->task]);
    }

    public function add(User $user, Task $task, $exportType)
    {
        if ($user->cannot('view', $task)) {
            return false;
        }

        if ($exportType === Export::TYPE_HTMLENTITIES && in_array($task->type, Task::USERS_LISTS_TYPES)) {
            return false;
        }

        return true;
    }
}
