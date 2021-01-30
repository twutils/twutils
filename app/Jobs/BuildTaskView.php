<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\Media;
use App\Models\Tweet;
use App\Models\TaskView;
use Illuminate\Support\Arr;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;

class BuildTaskView extends Job
{


    protected Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task->fresh();
    }

    public function handle()
    {
        $taskView = new TaskView([
            'task_id' => $this->task->id,
            'tweets_text_only' => 0,
        ]);

        if (! in_array($this->task->type, Task::TWEETS_LISTS_TYPES)) {
            return;
        }

        $months = [];

        $this->task->tweets()->chunk(100, function ($tweets) use (&$taskView, &$months) {
            foreach ($tweets as $tweet) {
                $taskView->count += 1;

                $monthPath = $tweet->tweet_created_at->year.'.'.$tweet->tweet_created_at->month;

                Arr::set(
                    $months,
                    $monthPath,
                    Arr::get($months, $monthPath) + 1
                );

                if ($tweet->media->isEmpty()) {
                    $taskView->tweets_text_only += 1;
                    continue;
                }

                $taskView->tweets_with_photos += $this->countMedia($tweet, Media::TYPE_PHOTO);

                $taskView->tweets_with_videos += $this->countMedia($tweet, Media::TYPE_VIDEO);

                $taskView->tweets_with_gifs += $this->countMedia($tweet, Media::TYPE_ANIMATED_GIF);
            }
        });

        $taskView->months = $months;

        $taskView->save();
    }

    protected function countMedia(Tweet $tweet, $type)
    {
        return $tweet->media
            ->filter(
                fn (Media $media) => $media->type === $type)
            ->isEmpty() ? 0 : 1;
    }
}
