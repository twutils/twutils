<?php

namespace App\Jobs;

use App\Tweet;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleaningAllTweetsAndTweeps implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->queue = 'cleaning';
    }

    public function handle()
    {
        $taskTweetQuery = DB::table('task_tweet')->select('tweet_id_str');
        $tweetsQuery = DB::table('tweets')->select('tweep_id_str');
        $followingsQuery = DB::table('followings')->select('tweep_id_str');
        $followersQuery = DB::table('followers')->select('tweep_id_str');

        Tweet::whereNotIn(
                'id_str',
                $taskTweetQuery
            )
            ->get()
            ->map
            ->delete();

        $tweepsQuery = DB::table('tweeps')
            ->whereNotIn(
                'id_str',
                $tweetsQuery
            )
            ->whereNotIn(
                'id_str',
                $followingsQuery
            )
            ->whereNotIn(
                'id_str',
                $followersQuery
            );

        $tweepsQuery->delete();
    }
}
