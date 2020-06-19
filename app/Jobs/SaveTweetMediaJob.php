<?php

namespace App\Jobs;

use App\Task;
use App\Tweet;
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

        $tweet = Tweet::where('id_str', $this->tweetIdStr)->get()->last();

        $tweetMedia = AssetsManager::saveTweetMedia($tweet->toArray(), $this->task->id);

        $tweet = $this->task->tweets->where('id_str', $tweet['id_str'])->first();
        $tweet->pivot->attachments = $tweetMedia;
        $tweet->pivot->save();
    }

    public function failed($e)
    {
    }
}
