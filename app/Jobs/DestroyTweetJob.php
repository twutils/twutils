<?php

namespace App\Jobs;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;

class DestroyTweetJob extends Job
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
        $destroyLikes = new DestroyTweetsOperation();

        $destroyLikes->doRequest($this->socialUser, $this->task, ['index' => $this->index, 'likesCollection' => $this->likesCollection]);
    }
}
