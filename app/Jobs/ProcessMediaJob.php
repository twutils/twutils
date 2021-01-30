<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\MediaFile;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessMediaJob extends Job
{
    protected $media;

    public $deleteWhenMissingModels = true;



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
            if ($mediaFile->status !== MediaFile::STATUS_INITIAL) {
                return;
            }

            $mediaFile->status = MediaFile::STATUS_STARTED;
            $mediaFile->save();
        });

        $this->media->status = Media::STATUS_SUCCESS;
        $this->media->save();
    }
}
