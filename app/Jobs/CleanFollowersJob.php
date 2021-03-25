<?php

namespace App\Jobs;

use App\Models\Follower;
use App\TwUtils\Base\Job;

class CleanFollowersJob extends Job
{
    private $task;

    public function __construct($task)
    {
        $this->queue = 'cleaning';
        $this->task = $task;
    }

    public function handle()
    {
        $followers = $this->task->followers;

        $uniqueFollowers = $followers->unique('tweep_id_str')->pluck('id');
        $toDelete = $followers->whereNotIn('id', $uniqueFollowers)->pluck('id')->toArray();

        Follower::destroy($toDelete);
    }
}
