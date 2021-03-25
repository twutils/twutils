<?php

namespace App\Jobs;

use App\Models\Following;
use App\TwUtils\Base\Job;

class CleanFollowingsJob extends Job
{
    private $task;

    public function __construct($task)
    {
        $this->queue = 'cleaning';
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
