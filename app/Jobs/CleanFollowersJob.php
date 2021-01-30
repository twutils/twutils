<?php

namespace App\Jobs;

use App\Models\Follower;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;

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
