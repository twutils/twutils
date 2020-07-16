<?php

namespace App\Jobs;

use App\Task;
use Illuminate\Bus\Queueable;
use App\TwUtils\AssetsManager;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SaveTweetMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $tweetIdStr;
    protected $task;
    protected $tweetIndex;
    protected $totalTweets;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $tweetIdStr, Task $task, $tweetIndex, $totalTweets)
    {
        $this->queue = 'media';
        $this->tweetIdStr = $tweetIdStr;
        $this->task = $task;

        $this->tweetIndex = $tweetIndex;
        $this->totalTweets = $totalTweets;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->task || in_array($this->task->status, ['broken', 'cancelled'])) {
            return;
        }

        $tweet = $this->task->tweets->where('id_str', $this->tweetIdStr)->first();

        (new AssetsManager)->saveTweetMedia($tweet->pivot);

        $this->zipEntities();
    }

    public function failed()
    {
        $this->zipEntities();
    }

    protected function zipEntities()
    {
        if ($this->tweetIndex === $this->totalTweets - 1) {
            dispatch(new ZipEntitiesJob($this->task));
        }
    }
}
