<?php

namespace App\Jobs;

use App\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanZippedEntitiesJob implements ShouldQueue
{
    private $taskId;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($taskId)
    {
        //
        $this->taskId = $taskId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disks = [
            'temporaryTasks',
            'tasks',
        ];

        foreach ($disks as $disk) {
            $path = \Storage::disk($disk)->path('');

            collect(\Storage::disk($disk)->files($this->taskId))
            ->each(
                function ($file) use ($path) {
                    fclose(fopen($path.$file, 'rb'));
                }
            );

            \Storage::disk($disk)->deleteDirectory($this->taskId);
        }
    }
}
