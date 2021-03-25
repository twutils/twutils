<?php

namespace App\TwUtils\Tweets\Media;

use App\Models\Media;
use App\Models\MediaFile;

abstract class Downloader
{
    protected Media $media;

    protected MediaFile $mediaFile;

    final public function __construct(MediaFile $mediaFile)
    {
        $this->media = $mediaFile->media;
        $this->mediaFile = $mediaFile;
    }

    abstract protected function getUrl(): string;

    final public function download(): MediaFile
    {
        try {
            $this->doDownload();

            $this->mediaFile->status = MediaFile::STATUS_SUCCESS;
        } catch (\Exception $e) {
            $this->mediaFile->status = MediaFile::STATUS_BROKEN;
        }

        $this->mediaFile->save();

        return $this->mediaFile;
    }

    final protected function doDownload(): void
    {
        $client = app('HttpClient');

        $response = $client->get($this->getUrl());

        $extension = app('MimeDB')->findExtension($response->getHeaderLine('Content-Type'));

        $this->mediaFile->extension = $extension;

        if (
            MediaFile::getStorageDisk()->exists($this->mediaFile->mediaPath) ||
            MediaFile::getCacheStorageDisk()->exists($this->mediaFile->mediaPath)
        ) {
            return;
        }

        $contents = $response->getBody()->getContents();

        MediaFile::getCacheStorageDisk()->put($this->mediaFile->mediaPath, $contents);

        if (
            MediaFile::getStorageDisk()->put(
                $this->mediaFile->mediaPath,
                $contents
            )
        ) {
            return;
        }

        throw new \Exception('Error downloading media file: '.$this->mediaFile->id);
    }
}
