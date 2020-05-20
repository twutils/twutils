<?php

namespace App\Jobs;

use App\TwUtils\TwitterOperations\destroyLikesOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DislikeTweetJob implements ShouldQueue
{
    private $socialUser;
    private $index;
    private $likesCollection;
    private $task;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $destroyLikes = new destroyLikesOperation();

        $destroyLikes->doRequest($this->socialUser, $this->task, ['index' => $this->index, 'likesCollection' => $this->likesCollection]);
    }
}
