<?php

namespace App\Jobs;

use App\Task;
use App\Media;
use App\Tweet;
use App\Export;
use Illuminate\Bus\Queueable;
use App\TwUtils\AssetsManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StartExportMediaJob implements ShouldQueue
{
    protected $export;

    public $deleteWhenMissingModels = true;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(Export $export)
    {
        $this->queue = 'exports';
        $this->export = $export;
    }

    public function handle()
    {
        $tweetsWithMedia = $this->export->task->fresh()->tweets
            ->filter(fn (Tweet $tweet) => AssetsManager::hasMedia($tweet))
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
                if ($media->status !== Media::STATUS_INITIAL)
                    return ;

                $media->status = Media::STATUS_STARTED;
                $media->save();
            });
        });
    }
}
