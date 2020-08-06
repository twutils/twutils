<?php

namespace App\Jobs;

use App\Download;
use App\MediaFile;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\TwUtils\ExportsManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ZipEntitiesJob implements ShouldQueue
{
    protected $download;

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
        try {
            $this->start();
        } catch (\Exception $e) {
            \Log::warning($e);

            $this->download->status = 'broken';
            $this->download->save();
        }
    }

    protected function start()
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

            if (Storage::disk('local')->exists($this->download->id.'/'.$path))
            {
                \Log::info('Skip Copying [' . $path . ']');

                return ;
            }

            if (MediaFile::getCacheStorageDisk()->exists($path))
            {
                \Log::info('Copying from cache [' . $path . '] to: ' . $this->download->id.'/'.$path);

                Storage::disk('local')->put($this->download->id.'/'.$path, MediaFile::getCacheStorageDisk()->readStream($path));

                return ;
            }

            \Log::info('Copying [' . $path . '] to: ' . $this->download->id.'/'.$path);

            try {
                Storage::disk('local')->put($this->download->id.'/'.$path, MediaFile::getStorageDisk()->readStream($path));
            } catch (\Exception $e) {
                Log::warning($e);
            }
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

        $this->download->status = 'success';

        $this->download->save();
    }
}
