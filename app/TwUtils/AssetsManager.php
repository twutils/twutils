<?php

namespace App\TwUtils;

use Image;
use Storage;
use App\Tweet;
use App\TaskTweet;
use Illuminate\Support\Arr;
use App\TwUtils\Tweets\Media\Downloader;
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
            $this->saveSingleTweetMedia((array) $media->data, $taskTweet, $tweetMedias, $medias);
        }
    }

    public function saveSingleTweetMedia(array $media, TaskTweet $taskTweet, & $tweetMedias, $medias)
    {
        $tweet = $taskTweet->tweet;
        $taskId = $taskTweet->task_id;

        $media = json_decode(json_encode($media));

        $mediaPath = $taskTweet->getMediaPathInStorage();

        $savedMedia = [];

        try {
            if ($media->type == 'photo') {
                $savedMedia = [static::saveTweetPhoto($media, $mediaPath)];
            } elseif ($media->type == 'video') {
                $savedMedia = [static::saveTweetPhoto($media, $mediaPath), static::saveTweetVideo($media, $mediaPath)];
            } elseif ($media->type == 'animated_gif') {
                $savedMedia = [static::saveTweetPhoto($media, $mediaPath), static::saveTweetGif($media, $mediaPath)];
            }
        } catch (\Exception $e) {
            \Log::info(json_encode(['exception' => $e.'', 'desc'=> sprintf('Couldn\'t download the media in the tweet [%s] for the task [%s] ', $tweet['id_str'], $taskId)]));
        }

        if (! empty($savedMedia)) {
            $savedMedia = (object) collect($savedMedia)
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
        }

        if (! empty($savedMedia)) {
            array_push($tweetMedias, $savedMedia);
        }


        $tweetMedia = ['type' => Arr::last($medias, null, ['type' => null])['type'], 'paths' => $tweetMedias];

        if ($tweetMedia['type'])
        {
            $taskTweet->attachments = $tweetMedia;
            $taskTweet->save();
        }
    }

    public static function saveTweetPhoto($media, $path)
    {
        return (new ImageDownloader($media, $path))->download()->toArray();
    }

    public static function saveTweetVideo($media, $path)
    {
        return (new VideoDownloader($media, $path))->download()->toArray();
    }

    public static function saveTweetGif($media, $path)
    {
        return (new GifDownloader($media, $path))->download()->toArray();
    }
}
