<?php

namespace App\Jobs;

use App\Task;
use App\Tweep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteTaskJob implements ShouldQueue
{
    private $task;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        //
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->task->fresh()->status !== 'completed') {
            $this->task->status = 'completed';
            $this->task->save();
        }

        $tweeps = $this->task->getTaskTweeps();

        $tweeps->map(function (Tweep $tweep) {
            dispatch(new SaveTweepAvatarJob($tweep->id_str));
        });
    }
}
