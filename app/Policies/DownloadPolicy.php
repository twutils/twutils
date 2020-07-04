<?php

namespace App\Policies;

use App\Task;
use App\User;
use App\Download;
use Illuminate\Auth\Access\HandlesAuthorization;

class DownloadPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Download $download, Task $task)
    {
        if ( $user->cannot('view', $task))
        {
            return false;
        }

        return $download->task_id === $task->id;
    }
}
