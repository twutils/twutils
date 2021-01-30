<?php

namespace App\Jobs;

use App\Models\MediaFile;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\TwUtils\Base\Job;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessMediaFileJob extends Job
{
    protected $mediaFile;

    public $deleteWhenMissingModels = true;



    public function __construct(MediaFile $mediaFile)
    {
        $this->queue = 'media';
        $this->mediaFile = $mediaFile;
    }

    public function handle()
    {
        if ($this->mediaFile->status !== MediaFile::STATUS_STARTED) {
            return;
        }

        $this->mediaFile->download();
    }
}
