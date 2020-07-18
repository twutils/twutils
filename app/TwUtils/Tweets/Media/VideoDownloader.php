<?php

namespace App\TwUtils\Tweets\Media;

class VideoDownloader extends Downloader
{
    public function getUrl() : string
    {
        $videoVariants = collect($this->media->raw['video_info']['variants']);
        $chosenVideo = $this->choseBestVideo($videoVariants);

        return $chosenVideo['url'];
    }

    protected function choseBestVideo($videoVariants)
    {
        $chosenVideo = $videoVariants->first();

        $mp4Videos = $videoVariants->filter(
            function ($item) {
                return $item['content_type'] == 'video/mp4';
            }
        );
        $minimumBitrate = $mp4Videos->min('bitrate');

        if (! is_null($minimumBitrate)) {
            $chosenVideo = $mp4Videos->first(
                function ($item) use ($minimumBitrate) {
                    return $item['bitrate'] == $minimumBitrate;
                }
            );
        }

        return $chosenVideo;
    }
}