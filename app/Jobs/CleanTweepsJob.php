<?php

namespace App\Jobs;

use App\Tweep;
use App\Tweet;
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
        ->sortByDesc('id')
        ->map(function ($tweep) use (&$uniqueTweeps) {
            if (in_array($tweep->id_str, array_keys($uniqueTweeps))) {
                return $tweep;
            }

            $uniqueTweeps[$tweep->id_str] = $tweep;

            return false;
        })
        // filter only the duplicates tweeps that will be removed eventually
        ->filter(function ($tweepOrFalse) {
            return $tweepOrFalse;
        })
        // group them by their 'id_str'
        ->groupBy('id_str')
        // Update tweets that associated with duplicate tweeps, to be assigned
        // to the newly created tweep.
        ->map(function ($similarIdStrsTweeps) use (&$uniqueTweeps) {
            Tweet::whereIn('tweep_id', $similarIdStrsTweeps->pluck('id')->toArray())
            ->update(['tweep_id' => $uniqueTweeps[$similarIdStrsTweeps->first()->id_str]->id]);

            return $similarIdStrsTweeps;
        })
        // get the Tweep Ids for the 'id_str' grouped tweeps
        ->map(function ($similarIdStrsTweeps) {
            return $similarIdStrsTweeps->pluck('id');
        })
        // Merge all Tweep ids into one variable
        ->map(function ($similarIdStrsTweeps) use (&$tweepsToDelete) {
            $tweepsToDelete = $tweepsToDelete->merge($similarIdStrsTweeps);
        });

        Tweep::whereIn('id', $tweepsToDelete->toArray())->delete();
    }
}
