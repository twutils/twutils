<?php

namespace App\Jobs;

use DB;
use App\Tweep;
use Illuminate\Bus\Queueable;
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
    }

    public function handle()
    {
        $taskTweetQuery = DB::table('task_tweet')->select('tweet_id_str');
        $tweetsQuery = DB::table('tweets')->select('tweep_id_str');
        $followingsQuery = DB::table('followings')->select('tweep_id_str');
        $followersQuery = DB::table('followers')->select('tweep_id_str');

        // If we are in production/development
        // Delay cleaning for one hour

        if (! app()->runningUnitTests())
        {
            $taskTweetQuery = $taskTweetQuery->where('created_at', '<' ,now()->subHours(1));
            $tweetsQuery = $tweetsQuery->where('created_at', '<' ,now()->subHours(1));
            $followingsQuery = $followingsQuery->where('created_at', '<' ,now()->subHours(1));
            $followersQuery = $followersQuery->where('created_at', '<' ,now()->subHours(1));
        }

        DB::table('tweets')
            ->whereNotIn(
                'id_str',
                $taskTweetQuery
            )
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
