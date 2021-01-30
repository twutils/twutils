<?php

namespace App\Jobs;

use App\Models\Following;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;

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
