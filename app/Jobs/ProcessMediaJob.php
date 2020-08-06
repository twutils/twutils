<?php

namespace App\Jobs;

use App\Media;
use App\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessMediaJob implements ShouldQueue
{
    protected $media;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(Media $media)
    {
        $this->queue = 'media';
        $this->media = $media;
    }

    public function handle()
    {
        if ($this->media->status !== Media::STATUS_STARTED) {
            return;
        }

        $this->media->mediaFiles->map(function (MediaFile $mediaFile) {
            $mediaFile->status = MediaFile::STATUS_STARTED;
            $mediaFile->save();
        });

        $this->media->status = Media::STATUS_SUCCESS;
        $this->media->save();
    }
}
