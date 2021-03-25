<?php

namespace App\TwUtils;

use App\Models\Media;
use App\Models\Tweet;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
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
            return;
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
    }

    public static function hasMedia(Tweet $tweet)
    {
        return ! empty($tweet['extended_entities']) && ! empty($tweet['extended_entities']['media']);
    }

    public static function getMediaDownloaders(Media $media): array
    {
        if ($media->type == Media::TYPE_PHOTO) {
            return [ImageDownloader::class];
        }

        if ($media->type == Media::TYPE_VIDEO) {
            return [ImageDownloader::class, VideoDownloader::class];
        }

        if ($media->type == Media::TYPE_ANIMATED_GIF) {
            return [ImageDownloader::class, GifDownloader::class];
        }
    }
}
