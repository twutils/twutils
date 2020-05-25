<?php

namespace App\Jobs;

use App\Tweep;
use App\Tweet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleanTweepJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tweepIdStr;

    public function __construct($tweepIdStr)
    {
        $this->queue = 'cleaning';
        $this->tweepIdStr = $tweepIdStr;
    }

    public function handle()
    {
        $tweepGroup = Tweep::where('id_str', $this->tweepIdStr)->orderByDesc('id')->get();

        $reservedTweep = $tweepGroup->shift();

        Tweet::whereIn('tweep_id', $tweepGroup->pluck('id')->toArray())
            ->update(['tweep_id' => $reservedTweep->id]);

        Tweep::whereIn('id', $tweepGroup->pluck('id')->toArray())->delete();
    }
}
