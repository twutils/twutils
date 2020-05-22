<?php

namespace App\Jobs;

use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchEntitiesLikesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
