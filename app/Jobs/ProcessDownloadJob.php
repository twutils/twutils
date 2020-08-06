<?php

namespace App\Jobs;

use App\Task;
use App\Download;
use App\MediaFile;
use Illuminate\Bus\Queueable;
use App\TwUtils\ExportsManager;
use App\Exports\TweetsListExport;
use App\Exports\UsersListTaskExport;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessDownloadJob implements ShouldQueue
{
    protected $download;
    protected $mediaFilesIsCompleted;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(Download $download)
    {
        $this->queue = 'downloads';
        $this->download = $download;
    }

    public function handle()
    {
        if ($this->download->status !== Download::STATUS_STARTED) {
            return;
        }

        if ($this->download->type === Download::TYPE_HTML) {
            $this->createHtmlDownload();
        }

        if ($this->download->type === Download::TYPE_EXCEL) {
            $this->createExcelDownload();
        }

        if ($this->download->type === Download::TYPE_HTMLENTITIES) {
            $this->createHtmlEntitiesDownload();
        }
    }

    protected function success()
    {
        $this->download->status = 'success';
        $this->download->save();
    }

    protected function createHtmlDownload()
    {
        ExportsManager::createHtmlZip($this->download);

        $this->success();
    }

    protected function createExcelDownload()
    {
        $task = $this->download->task;

        if (in_array($task->type, Task::USERS_LISTS_TYPES)) {
            return (new UsersListTaskExport($task))->store($this->download->id, config('filesystems.cloud'), \Maatwebsite\Excel\Excel::XLSX);
        }

        $tweets = collect([]);

        if (in_array($task->type, Task::TWEETS_LISTS_TYPES)) {
            $tweets = $task->likes;
        }

        if (in_array($task->baseName, Task::TWEETS_MANAGED_DESTROY_BASE_NAMES)) {
            $task = Task::where('managed_by_task_id', $task->id)->get()
                ->first(function (Task $task) {
                    return in_array($task->baseName, Task::TWEETS_DESTROY_BASE_NAMES);
                });
        }

        if (
            in_array($task->baseName, Task::TWEETS_DESTROY_BASE_NAMES) &&
            ($targetedTask = $task->targetedTask)
        ) {
            $tweets = $targetedTask
                ->tweets()
                ->wherePivot('removed', '!=', null)
                ->get();
        }

        if (
            (new TweetsListExport($tweets))->store(
                $this->download->id,
                config('filesystems.cloud'), \Maatwebsite\Excel\Excel::XLSX
            )
        ) {
            $this->success();
        }
    }

    protected function createHtmlEntitiesDownload()
    {
        $this->mediaFilesIsCompleted = true;

        $this->download->task
            ->likes
            ->load('media.mediaFiles')
            ->pluck('media.*.mediaFiles.*')
            ->map(function ($mediaFilesCollection) {
                return collect($mediaFilesCollection)->map(function ($i) {
                    if (in_array($i->status, [MediaFile::STATUS_STARTED, MediaFile::STATUS_INITIAL])) {
                        $this->mediaFilesIsCompleted = false;
                    }
                });
            });

        if (! $this->mediaFilesIsCompleted) {
            return dispatch(new self($this->download))->delay(now()->addSeconds(10));
        }

        (new ZipEntitiesJob($this->download))->handle();
    }
}
