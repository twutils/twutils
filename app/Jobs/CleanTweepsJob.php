<?php

namespace App\Jobs;

use App\Tweep;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanTweepsJob implements ShouldQueue
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
        // all tweeps who inserted more than once
        $duplicates = DB::select('select distinct * from tweeps, (select tweeps.id_str, count(tweeps.id_str) as count  from tweeps group by tweeps.id_str) as subquery where subquery.count > 1 and tweeps.id_str = subquery.id_str');

        $uniqueTweeps = [];
        $tweepsToDelete = collect([]);

        // mark the unique tweep as the last inserted record, newly created.
        collect($duplicates)
        ->groupBy('id_str')
        ->map(function ($duplicateGroup) {
            dispatch(
                new CleanTweepJob(
                    $duplicateGroup->first()->id_str,
                )
            );
        });
    }
}
