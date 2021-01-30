<?php

namespace App\Jobs;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;

class FetchEntitiesLikesJob extends Job
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
        $likes = new FetchEntitiesLikesOperation();

        $likes->doRequest($this->socialUser, $this->task, $this->parameters);
    }
}
