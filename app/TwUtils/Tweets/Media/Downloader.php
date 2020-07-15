<?php

namespace App\TwUtils\Tweets\Media;

use App\TwUtils\State\Media;
use App\TwUtils\State\DownloadStatus;
use Illuminate\Support\Facades\Storage;

abstract class Downloader
{
    protected $media;
    protected $taskTweet;
    protected $path;
    static $counter = 0;

    final public function __construct(Media $media, $taskTweet)
    {
        $this->media = $media->data;
        $this->taskTweet = $taskTweet;
        $this->path = $this->taskTweet->getMediaPathInStorage() . ++ static::$counter;
    }

    abstract protected function getUrl() : string;

    final public function download() : DownloadStatus
    {
        $ok = false;

        $localPath = null;

        try {
            $ok = $this->doDownload($localPath);
        } catch (\Exception $e) {}

        return new DownloadStatus($ok, $localPath);
    }

    final protected function doDownload(& $localPath) : bool
    {
        $client = app('HttpClient');

        $response = $client->get($this->getUrl());

        $extension = app('MimeDB')->findExtension($response->getHeaderLine('Content-Type'));
        $localPath = $this->path.'.'.$extension;

        if (Storage::disk('temporaryTasks')->put($localPath, $response->getBody()->getContents())) {
            return true;
        }

        return false;
    }
}