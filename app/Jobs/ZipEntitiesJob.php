<?php

namespace App\Jobs;

use App\TwUtils\ExportsManager;
use \Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZipEntitiesJob implements ShouldQueue
{
    private $task;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        dispatch(new CompleteTaskJob($this->task));

        $savedMediaPath = \Storage::disk('temporaryTasks')->path($this->task->id);
        $zippedTaskPath = \Storage::disk('tasks')->path($this->task->id);
        $fileName = $this->task->socialUser->nickname.'_'.date('d-m-Y_H-i-s').'.zip';
        $fileAbsolutePath = $zippedTaskPath.'/'.$fileName;

        // Mark task as 'completed', even if the previously
        // dispatched job wasn't processed, so the frontend
        // rendering (during the HTML zip) will see it as
        // completed
        $this->task->status = 'completed';

        $zipFile = ExportsManager::makeTaskZipObject($this->task);

        // Include media in the zip file, and save it
        foreach (collect(\Storage::disk('temporaryTasks')->allFiles($this->task->id))
        ->chunk(5) as $filesChunk) {
            $filesChunk->map(function ($file) use (& $zipFile) {
                $zipFile->addFile(\Storage::disk('temporaryTasks')->path($file), 'media/' . Str::after($file, '/'));
            });
        }

        $zipFile
        ->saveAsFile($fileAbsolutePath)
        ->close();

        $zippedStream = fopen($fileAbsolutePath, 'r');

        \Storage::disk(config('filesystems.cloud'))->put($this->task->id.'/'.$fileName, $zippedStream);

        fclose($zippedStream);

        dispatch(new CleanZippedEntitiesJob($this->task->id))->delay(now()->addSeconds(1));
    }
}
