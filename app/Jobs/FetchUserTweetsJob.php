<?php

namespace App\Jobs;

use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchUserTweetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $likes = new FetchUserTweetsOperation();

        $likes->doRequest($this->socialUser, $this->task, $this->parameters);
    }
}
