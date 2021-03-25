<?php

namespace App\Jobs;

use App\Models\Task;
use App\TwUtils\Base\Job;
use App\Models\SocialUser;

class CompleteManagedDestroyLikesJob extends Job
{
    protected $managedTask;

    protected $socialUser;

    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Task $managedTask, SocialUser $socialUser, Task $task)
    {
        $this->managedTask = $managedTask;
        $this->socialUser = $socialUser;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $destroyLikes = new $this->task->type();

        $destroyLikes->complete($this->managedTask->fresh(), $this->socialUser, $this->task);
    }
}
