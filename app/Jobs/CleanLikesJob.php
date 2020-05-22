<?php

namespace App\Jobs;

use App\TaskTweet;
use App\Tweet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanLikesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        $taskTweets = [];
        $duplicateTaskTweetsRelationIds = [];

        $likes = $this->task ? $this->task->likes : Tweet::all();

        $uniqueLikes = $likes->unique('id_str')->pluck('id');
        $toDelete = $likes->whereNotIn('id', $uniqueLikes);
        $toDeleteIds = $toDelete->pluck('id')->toArray();

        if (count($toDeleteIds) > 0) {
            collect($toDeleteIds)->chunk(config('twutils.database_groups_chunk_counts.tweep_db_where_in_limit'))
            ->each(function ($toDeleteIdsGroup) {
                Tweet::destroy($toDeleteIdsGroup);
            });
        }

        TaskTweet::where('task_id', $this->task->id)
        ->get()
        ->each(function ($taskTweet) use (&$taskTweets, &$duplicateTaskTweetsRelationIds) {
            if (in_array($tweetId = $taskTweet->tweet_id_str, $taskTweets)) {
                array_push($duplicateTaskTweetsRelationIds, $taskTweet->id);
            }

            array_push($taskTweets, $tweetId);
        });

        if (count($duplicateTaskTweetsRelationIds) > 0) {
            collect($duplicateTaskTweetsRelationIds)->chunk(config('twutils.database_groups_chunk_counts.tweep_db_where_in_limit'))
            ->each(function ($duplicateTaskTweetsRelationIdsGroup) {
                TaskTweet::whereIn('id', $duplicateTaskTweetsRelationIdsGroup)->delete();
            });
        }

        dispatch(new CleanTweepsJob());
    }
}
