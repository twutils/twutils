<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TwUtils\TwitterOperations\FetchFollowingLookupsOperation;

class FetchFollowingLookupsJob implements ShouldQueue
{
    private $parameters;
    private $socialUser;
    private $task;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $fetchFollowingLookupOperation = new FetchFollowingLookupsOperation();

        $fetchFollowingLookupOperation->doRequest($this->socialUser, $this->task, $this->parameters);
    }
}
