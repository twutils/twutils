<?php

namespace App\Jobs;

use App\TwUtils\Base\Job;
use App\TwUtils\TwitterOperations\DestroyLikesOperation;

class DislikeTweetJob extends Job
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
    public function __construct($socialUser, $index, $likesCollection, $task)
    {
        $this->socialUser = $socialUser;
        $this->index = (int) $index;
        $this->likesCollection = $likesCollection;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $destroyLikes = new DestroyLikesOperation();

        $destroyLikes->doRequest($this->socialUser, $this->task, ['index' => $this->index, 'likesCollection' => $this->likesCollection]);
    }
}
