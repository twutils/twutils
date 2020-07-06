<?php

namespace App\TwUtils\Tweets\Media;

class ImageDownloader extends Downloader
{
    public function getUrl() : string
    {
        return $this->media->media_url_https;
    }
}