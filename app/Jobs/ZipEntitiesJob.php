<?php

namespace App\Jobs;

use App\Download;
use App\MediaFile;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\TwUtils\ExportsManager;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ZipEntitiesJob implements ShouldQueue
{
    private $download;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(Download $download)
    {
        $this->download = $download;
    }

    public function handle()
    {
        $paths = collect();
        $this->download
            ->task
            ->likes
            ->load('media.mediaFiles')
            ->pluck('media.*.mediaFiles.*')
            ->map(function ($mediaFilesCollection) use ($paths) {
                return collect($mediaFilesCollection)->map(function ($mediaFile) use ($paths) {
                    if ($mediaFile->status === MediaFile::STATUS_SUCCESS) {
                        $paths->push($mediaFile->mediaPath);
                    }
                });
            });

        Storage::disk('local')->makeDirectory($this->download->id);

        $paths->map(function ($path) {
            Storage::disk('local')->put($this->download->id.'/'.$path, MediaFile::getStorageDisk()->readStream($path));
        });

        $fileName = $this->download->task->socialUser->nickname.'_'.date('d-m-Y_H-i-s').'.zip';

        $fileAbsolutePath = Storage::disk('local')->path($this->download->id).'/'.$fileName;

        $zipFile = ExportsManager::makeTaskZipObject($this->download->task);

        // Include media in the zip file, and save it
        foreach (collect(Storage::disk('local')->allFiles($this->download->id))
        ->chunk(5) as $filesChunk) {
            $filesChunk->map(function ($file) use (&$zipFile) {
                $zipFile->addFile(Storage::disk('local')->path($file), 'media/'.Str::after($file, '/'));
            });
        }

        $zipFile
        ->saveAsFile($fileAbsolutePath)
        ->close();

        $zippedStream = fopen($fileAbsolutePath, 'r');

        Storage::disk(config('filesystems.cloud'))->put($this->download->id, $zippedStream);

        fclose($zippedStream);

        Storage::disk('local')->deleteDirectory($this->download->id);
    }
}
