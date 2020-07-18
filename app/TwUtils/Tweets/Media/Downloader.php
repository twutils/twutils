<?php

namespace App\TwUtils\Tweets\Media;

use App\Media;
use App\MediaFile;
use Illuminate\Support\Facades\Storage;

abstract class Downloader
{
    protected Media $media;
    protected MediaFile $mediaFile;

    final public function __construct(MediaFile $mediaFile)
    {
        $this->media = $mediaFile->media;
        $this->mediaFile = $mediaFile;
    }

    abstract protected function getUrl() : string;

    final public function download() : MediaFile
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

    final protected function doDownload() : void
    {
        $client = app('HttpClient');

        $response = $client->get($this->getUrl());

        $extension = app('MimeDB')->findExtension($response->getHeaderLine('Content-Type'));

        if (Storage::disk('tweetsMedia')->put($this->mediaFile->id . '.' . $extension, $response->getBody()->getContents())) {
            $this->mediaFile->extension = $extension;

            return ;
        }

        throw new \Exception("Error downloading media file: " . $this->mediaFile->id);
        
    }
}