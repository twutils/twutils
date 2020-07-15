<?php

namespace App\TwUtils;

use Image;
use Storage;
use App\Tweet;
use App\TaskTweet;
use Illuminate\Support\Arr;
use App\TwUtils\State\Media;
use App\TwUtils\Tweets\Media\GifDownloader;
use App\TwUtils\Tweets\Media\ImageDownloader;
use App\TwUtils\Tweets\Media\VideoDownloader;

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

        if (! is_null($width) && ! is_null($height)) {
            $image = $image->resize($width, $height);
        } elseif (! is_null($width) && is_null($height)) {
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

    public static function hasMedia(Tweet $tweet)
    {
        return ! empty($tweet['extended_entities']) && ! empty($tweet['extended_entities']['media']);
    }

    public function saveTweetMedia(TaskTweet $taskTweet)
    {
        $tweet = $taskTweet->tweet;

        $tweetMedias = [];

        $medias = Arr::get($tweet, 'extended_entities.media', []);

        foreach ($taskTweet->getMedia() as $media) {
            $this->saveSingleTweetMedia($media, $taskTweet, $tweetMedias, $medias);
        }
    }

    public function saveSingleTweetMedia($media, TaskTweet $taskTweet, & $tweetMedias, $medias)
    {
        $tweet = $taskTweet->tweet;
        $taskId = $taskTweet->task_id;

        $mediaPath = $taskTweet->getMediaPathInStorage();

        $savedMedia = [];

        if ($media->type == 'photo') {
            $savedMedia = [static::saveTweetPhoto($media, $mediaPath)];
        } elseif ($media->type == 'video') {
            $savedMedia = [static::saveTweetPhoto($media, $mediaPath), static::saveTweetVideo($media, $mediaPath)];
        } elseif ($media->type == 'animated_gif') {
            $savedMedia = [static::saveTweetPhoto($media, $mediaPath), static::saveTweetGif($media, $mediaPath)];
        }

        $savedMedia = (array) collect($savedMedia)
            ->filter(
                function ($item) {
                    return $item['ok'];
                }
            )
            ->pluck('path')
            ->map(function ($mediaPath) use ($taskTweet) {
                return substr($mediaPath, strlen($taskTweet->getMediaDirPathInStorage()));
            })
            ->toArray();

        array_push($tweetMedias, $savedMedia);


        $taskTweet->attachments_type = Arr::last($medias, null, null);

        if ( $taskTweet->attachments_type )
        {
            $taskTweet->attachments_paths = $tweetMedias;
        }

        $taskTweet->save();
    }

    public static function saveTweetPhoto(Media $media, $path)
    {
        return (new ImageDownloader($media, $path))->download()->toArray();
    }

    public static function saveTweetVideo(Media $media, $path)
    {
        return (new VideoDownloader($media, $path))->download()->toArray();
    }

    public static function saveTweetGif(Media $media, $path)
    {
        return (new GifDownloader($media, $path))->download()->toArray();
    }
}
