<?php

namespace App\Jobs;

use App\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessMediaFileJob implements ShouldQueue
{
    protected $mediaFile;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
