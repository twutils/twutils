<?php

namespace AppNext\Jobs;

use App\Models\Task;
use App\TwUtils\Base\Job;

class DestroyRawTweetJob extends Job
{
    private $socialUser;

    private $index;

    private $likesCollection;

    private $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $destroyLikes = new DestroyTweetsOperation();

        $destroyLikes->doRequest($this->socialUser, $this->task, ['index' => $this->index, 'likesCollection' => $this->likesCollection]);
    }
}
