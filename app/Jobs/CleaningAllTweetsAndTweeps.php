<?php

namespace App\Jobs;

use App\Models\Tweet;
use App\TwUtils\Base\Job;
use Illuminate\Support\Facades\DB;

class CleaningAllTweetsAndTweeps extends Job
{
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
