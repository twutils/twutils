<?php

namespace App\Jobs;

use App\SocialUser;
use App\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteManagedDestroyLikesJob implements ShouldQueue
{
    protected $managedTask;
    protected $socialUser;
    protected $task;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $destroyLikes = new $this->task->type;

        $destroyLikes->complete($this->managedTask->fresh(), $this->socialUser, $this->task);
    }
}
