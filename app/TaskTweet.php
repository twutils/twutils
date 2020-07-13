<?php

namespace App;

use Illuminate\Support\Arr;
use App\TwUtils\State\Media;
use App\TwUtils\Tweets\Media\Downloader;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTweet extends Pivot
{
    protected $table = 'task_tweet';

    protected $casts = [
        'task_id' => 'int',
        'attachments' => 'array',
    ];

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'tweet_id_str', 'id_str');
    }

    public function getMedia()
    {
        $tweet = $this->tweet;

        $tweetMedias = [];

        Downloader::$counter = 0;

        $medias = Arr::get($tweet, 'extended_entities.media', []);

        $type = Arr::last($medias, null, ['type' => null])['type'];

        if ($type === null)
        {
            return [];
        }

        foreach ($medias as $media) {
            $media = json_decode(json_encode($media));
            $tweetMedias[] = new Media($media, $type);
        }

        return $tweetMedias;
    }

    public function getMediaDirPathInStorage()
    {
        return $this->task_id . '/';
    }

    public function getMediaPathInStorage()
    {
        return $this->getMediaDirPathInStorage() . $this->tweet_id_str . '_';
    }

}
