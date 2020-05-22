<?php

namespace App\TwUtils;

use Image;
use Storage;

class AssetsManager
{
    public static function storeAvatar($url, $socialUserId)
    {
        return static::storeImage($url, $socialUserId.'.png', 100, 100);
    }

    public static function storeImage($url, $name, $width = null, $height = null, $publicDirectory = 'users')
    {
        if (is_null($url)) {
            return null;
        }

        $tempPath = storage_path($name);
        $newPath = "{$publicDirectory}/{$name}";

        $image = Image::make($url);

        if (!is_null($width) && !is_null($height)) {
            $image = $image->resize($width, $height);
        } elseif (!is_null($width) && is_null($height)) {
            $image = $image->widen($width);
        }

        $image = $image->save($tempPath);

        $url = Storage::disk('public')->put($newPath, $image);

        @unlink($tempPath);

        if ($url) {
            return $newPath;
        }

        return null;
    }

    public static function hasMedia(array $tweet)
    {
        return !empty($tweet['extended_entities']) && !empty($tweet['extended_entities']['media']);
    }

    public static function saveTweetMedia(array $tweet, $taskId)
    {
        if (!static::hasMedia($tweet)) {
            return [];
        }

        $tweetMedias = [];
        $path = $taskId.'/';

        $counter = 0;
        foreach ($tweet['extended_entities']['media'] as $media) {
            $media = json_decode(json_encode($media));

            $mediaPath = $tweet['id_str'].'_'.++$counter;

            $savedMedia = [];

            try {
                if ($media->type == 'photo') {
                    $savedMedia = [static::saveTweetPhoto($media, $path.$mediaPath)];
                } elseif ($media->type == 'video') {
                    $savedMedia = [static::saveTweetPhoto($media, $path.$mediaPath), static::saveTweetVideo($media, $path.$mediaPath)];
                } elseif ($media->type == 'animated_gif') {
                    $savedMedia = [static::saveTweetPhoto($media, $path.$mediaPath), static::saveTweetGif($media, $path.$mediaPath)];
                }
            } catch (\Exception $e) {
                \Log::info(json_encode(['exception' => $e.'', 'desc'=> sprintf('Couldn\'t download the media in the tweet [%s] for the task [%s] ', $tweet['id_str'], $taskId)]));

                return [];
            }

            if (!empty($savedMedia)) {
                $savedMedia = (object) collect($savedMedia)
                    ->filter(
                        function ($item) {
                            return $item['ok'];
                        }
                    )
                    ->pluck('path')
                    ->map(function ($mediaPath) use ($path) {
                        return substr($mediaPath, strlen($path));
                    })
                    ->toArray();
                array_push($tweetMedias, $savedMedia);
            }
        }

        return ['type' => $media->type, 'paths' => $tweetMedias];
    }

    public static function saveTweetPhoto($media, $path)
    {
        $ok = false;
        $client = app('HttpClient');
        $response = $client->get($media->media_url_https);

        $extension = app('MimeDB')->findExtension($response->getHeaderLine('Content-Type'));
        $localPath = $path.'.'.$extension;

        try {
            if (Storage::disk('temporaryTasks')->put($localPath, $response->getBody()->getContents())) {
                $ok = true;
            }
        } catch (\Exception $e) {
        }

        return ['ok' => $ok, 'path' => $localPath];
    }

    public static function saveTweetVideo($media, $path)
    {
        $ok = false;
        $client = app('HttpClient');

        $videoVariants = collect($media->video_info->variants);
        $chosenVideo = static::choseBestVideo($videoVariants);

        $response = $client->get($chosenVideo->url);

        $extension = app('MimeDB')->findExtension($response->getHeaderLine('Content-Type'));
        $localPath = $path.'.'.$extension;

        try {
            if (Storage::disk('temporaryTasks')->put($localPath, $response->getBody()->getContents())) {
                $ok = true;
            }
        } catch (\Exception $e) {
        }

        return ['ok' => $ok, 'path' => $localPath];
    }

    public static function saveTweetGif($media, $path)
    {
        $ok = false;
        $client = app('HttpClient');

        $video = $media->video_info->variants[0];
        $response = $client->get($video->url);

        $extension = app('MimeDB')->findExtension($response->getHeaderLine('Content-Type'));
        $localPath = $path.'.'.$extension;

        try {
            if (Storage::disk('temporaryTasks')->put($localPath, $response->getBody()->getContents())) {
                $ok = true;
            }
        } catch (\Exception $e) {
        }

        return ['ok' => $ok, 'path' => $localPath];
    }

    public static function choseBestVideo($videoVariants)
    {
        $chosenVideo = $videoVariants->first();

        $mp4Videos = $videoVariants->filter(
            function ($item) {
                return $item->content_type == 'video/mp4';
            }
        );
        $minimumBitrate = $mp4Videos->min('bitrate');
        if (!is_null($minimumBitrate)) {
            $chosenVideo = $mp4Videos->first(
                function ($item) use ($minimumBitrate) {
                    return $item->bitrate == $minimumBitrate;
                }
            );
        }

        return $chosenVideo;
    }
}
