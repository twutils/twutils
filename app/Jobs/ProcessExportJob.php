<?php

namespace App\Jobs;

use Exception;
use App\Models\Export;
use App\Models\MediaFile;
use App\TwUtils\Base\Job;
use Illuminate\Support\Str;
use App\TwUtils\Exports\LikesExport;
use App\TwUtils\Exports\TweetsExport;
use App\TwUtils\Exports\FollowersExport;
use App\TwUtils\Services\ExportsService;
use App\TwUtils\Exports\FollowingsExport;
use App\TwUtils\Base\Export as ExcelExport;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\DestroyLikesOperation;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation;
use App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation;

class ProcessExportJob extends Job
{
    protected $export;

    protected $mediaFilesIsCompleted;

    public $deleteWhenMissingModels = true;

    protected ExportsService $exportsService;

    public function __construct(Export $export)
    {
        $this->queue = 'exports';
        $this->export = $export;
        $this->exportsService = app(ExportsService::class);
    }

    public function handle()
    {
        try {
            $this->init();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    protected function init()
    {
        $this->export = $this->export->fresh();
        if (
            $this->export->type === Export::TYPE_HTMLENTITIES &&
            in_array($this->export->status, [Export::STATUS_INITIAL, Export::STATUS_STARTED])
        ) {
            dispatch(new StartExportMediaJob($this->export));
        }

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

    protected function handleException(Exception $e)
    {
        if ($this->export->status !== Export::STATUS_BROKEN) {
            $this->export->status = Export::STATUS_BROKEN;
            $this->export->exception = Str::limit($e->__toString(), 10000);
            $this->export->save();
        }
    }

    protected function success()
    {
        $this->export->status = 'success';
        $this->export->save();
    }

    protected function createHtmlExport()
    {
        $this->exportsService->createHtmlZip($this->export);

        $this->success();
    }

    protected function createExcelExport()
    {
        $task = $this->export->task;

        /** var ExcelExport */
        $exporter = match ($task->type) {
            FetchFollowingOperation::class => new FollowingsExport($task),
            FetchFollowersOperation::class => new FollowersExport($task),

            DestroyTweetsOperation::class,
            FetchUserTweetsOperation::class,
            FetchEntitiesUserTweetsOperation::class, => new TweetsExport($task),

            DestroyLikesOperation::class,
            FetchLikesOperation::class,
            FetchEntitiesLikesOperation::class => new LikesExport($task),
        };

        if (
            $exporter &&
            $exporter->store(
                $this->export->id,
                config('filesystems.cloud'),
                \Maatwebsite\Excel\Excel::XLSX,
            )
        ) {
            $this->success();
        }
    }

    protected function createHtmlEntitiesExport()
    {
        $this->mediaFilesIsCompleted = true;

        $this->export->progress = 0;

        $this->export->task
            ->fresh()
            ->getTweetsQuery()
            ->with('media.mediaFiles')
            ->get()
            ->pluck('media.*.mediaFiles.*')
            ->map(function ($mediaFilesCollection) {
                return collect($mediaFilesCollection)->map(function (MediaFile $mediaFile) {
                    if (in_array($mediaFile->status, [MediaFile::STATUS_STARTED, MediaFile::STATUS_INITIAL])) {
                        $this->mediaFilesIsCompleted = false;
                    }

                    if (in_array($mediaFile->status, [MediaFile::STATUS_SUCCESS])) {
                        $this->export->progress += 1;
                    }
                });
            });

        $this->export->save();

        if (! $this->mediaFilesIsCompleted) {
            return dispatch(new self($this->export))->delay(now()->addSeconds(5));
        }

        (new ZipEntitiesJob($this->export))->handle();
    }
}
