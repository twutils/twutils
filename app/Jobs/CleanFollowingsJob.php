<?php

namespace App\Jobs;

use App\Following;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleanFollowingsJob implements ShouldQueue
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
        $followings = $this->task->followings;

        $uniqueFollowings = $followings->unique('tweep_id_str')->pluck('id');
        $toDelete = $followings->whereNotIn('id', $uniqueFollowings)->pluck('id')->toArray();

        Following::destroy($toDelete);
    }
}
