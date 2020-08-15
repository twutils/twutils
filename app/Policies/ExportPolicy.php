<?php

namespace App\Policies;

use App\Task;
use App\User;
use App\Export;
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
}
