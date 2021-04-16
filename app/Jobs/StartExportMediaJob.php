<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\Tweet;
use App\Models\Export;
use App\TwUtils\Base\Job;
use App\TwUtils\Services\AssetsService;

class StartExportMediaJob extends Job
{
    protected $export;

    public $deleteWhenMissingModels = true;

    protected AssetsService $assetsManager;

    public function __construct(Export $export)
    {
        $this->queue = 'exports';
        $this->export = $export;
        $this->assetsManager = app(AssetsService::class);
    }

    public function handle()
    {
        $tweetsWithMedia = $this->export->task->fresh()->tweets
            ->filter(fn (Tweet $tweet) => $this->assetsManager->hasMedia($tweet))
            ->values();

        $mediaFiles = $this->export->task->fresh()
            ->tweets()
            ->with('media.mediaFiles')
            ->get()
            ->pluck('media.*.mediaFiles.*')
            ->map(function ($mediaFilesCollection) {
                return count($mediaFilesCollection);
            })
            ->sum();

        $this->export->progress_end = $mediaFiles + ceil($mediaFiles * 0.1); // Add 10% progress margin for zipping and uploading process.
        $this->export->save();

        $tweetsWithMedia->map(function ($tweet) {
            $tweet->media->map(function (Media $media) {
                if ($media->status !== Media::STATUS_INITIAL) {
                    return;
                }

                $media->status = Media::STATUS_STARTED;
                $media->save();
            });
        });
    }
}
