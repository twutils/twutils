<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TwUtils\TwitterOperations\destroyLikesOperation;

class DislikeTweetJob implements ShouldQueue
{
    private $socialUser;
    private $index;
    private $likesCollection;
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
