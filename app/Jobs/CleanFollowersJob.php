<?php

namespace App\Jobs;

use App\Follower;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanFollowersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $task;

    public function __construct($task)
    {
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
