<?php

namespace App\Jobs;

use App\TwUtils\Base\Job;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;

class FetchFollowersJob extends Job
{
    private $parameters;

    private $socialUser;

    private $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($parameters, $socialUser, $task)
    {
        $this->parameters = $parameters;
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
        $likes = new FetchFollowersOperation();

        $likes->doRequest($this->socialUser, $this->task, $this->parameters);
    }
}
