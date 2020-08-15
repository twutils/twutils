<?php

namespace App\Jobs;

use App\Task;
use App\Export;
use App\MediaFile;
use Illuminate\Bus\Queueable;
use App\TwUtils\ExportsManager;
use App\Exports\TweetsListExport;
use App\Exports\UsersListTaskExport;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessExportJob implements ShouldQueue
{
    protected $export;
    protected $mediaFilesIsCompleted;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(Export $export)
    {
        $this->queue = 'exports';
        $this->export = $export;
    }

    public function handle()
    {
        if ($this->export->status !== Export::STATUS_STARTED) {
            return;
        }

        if ($this->export->type === Export::TYPE_HTML) {
            $this->createHtmlExport();
        }

        if ($this->export->type === Export::TYPE_EXCEL) {
            $this->createExcelExport();
        }

        if ($this->export->type === Export::TYPE_HTMLENTITIES) {
            $this->createHtmlEntitiesExport();
        }
    }

    protected function success()
    {
        $this->export->status = 'success';
        $this->export->save();
    }

    protected function createHtmlExport()
    {
        ExportsManager::createHtmlZip($this->export);

        $this->success();
    }

    protected function createExcelExport()
    {
        $task = $this->export->task;

        if (in_array($task->type, Task::USERS_LISTS_TYPES)) {
            return (new UsersListTaskExport($task))->store($this->export->id, config('filesystems.cloud'), \Maatwebsite\Excel\Excel::XLSX);
        }

        $tweets = collect([]);

        if (in_array($task->type, Task::TWEETS_LISTS_TYPES)) {
            $tweets = $task->likes;
        }

        if (in_array($task->type, Task::TWEETS_MANAGED_DESTROY_TYPES)) {
            $task = Task::where('managed_by_task_id', $task->id)->get()
                ->first(function (Task $task) {
                    return in_array($task->type, Task::TWEETS_DESTROY_TWEETS_TYPES);
                });
        }

        if (
            in_array($task->type, Task::TWEETS_DESTROY_TWEETS_TYPES) &&
            ($targetedTask = $task->targetedTask)
        ) {
            $tweets = $targetedTask
                ->tweets()
                ->wherePivot('removed', '!=', null)
                ->get();
        }

        if (
            (new TweetsListExport($tweets))->store(
                $this->export->id,
                config('filesystems.cloud'), \Maatwebsite\Excel\Excel::XLSX
            )
        ) {
            $this->success();
        }
    }

    protected function createHtmlEntitiesExport()
    {
        $this->mediaFilesIsCompleted = true;

        $this->export->task
            ->likes
            ->load('media.mediaFiles')
            ->pluck('media.*.mediaFiles.*')
            ->map(function ($mediaFilesCollection) {
                return collect($mediaFilesCollection)->map(function (MediaFile $mediaFile) {
                    if (in_array($mediaFile->status, [MediaFile::STATUS_STARTED, MediaFile::STATUS_INITIAL])) {
                        $this->mediaFilesIsCompleted = false;
                    }
                });
            });

        if (! $this->mediaFilesIsCompleted) {
            return dispatch(new self($this->export))->delay(now()->addSeconds(10));
        }

        (new ZipEntitiesJob($this->export))->handle();
    }
}
