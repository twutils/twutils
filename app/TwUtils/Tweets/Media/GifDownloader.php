<?php

namespace App\TwUtils\Tweets\Media;

class GifDownloader extends Downloader
{
    public function getUrl() : string
    {
        $video = $this->media->raw['video_info']['variants'][0];

        return $video['url'];
    }
}