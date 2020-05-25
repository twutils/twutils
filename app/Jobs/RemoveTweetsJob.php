<?php

namespace App\Jobs;

use App\Tweet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RemoveTweetsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $toDeleteIdsGroup;

    public function __construct($toDeleteIdsGroup)
    {
        $this->queue = 'cleaning';
        $this->toDeleteIdsGroup = $toDeleteIdsGroup;
    }

    public function handle()
    {
        Tweet::destroy($this->toDeleteIdsGroup);
    }
}
