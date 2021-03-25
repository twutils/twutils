<?php

namespace App\Jobs;

use App\Models\Task;
use App\TwUtils\Base\Job;

class CompleteTaskJob extends Job
{
    private $task;

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

        $tweeps->map(function ($tweep) {
            if (! $tweep) {
                return;
            }

            dispatch(new SaveTweepAvatarJob($tweep->id_str));
        });

        dispatch(new CleaningAllTweetsAndTweeps);
    }
}
