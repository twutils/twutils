<?php

namespace App;

use App\Media;
use Illuminate\Support\Arr;
use App\TwUtils\State\Media;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTweet extends Pivot
{
    protected $table = 'task_tweet';

    protected $appends = ['attachments'];

    protected $casts = [
        'task_id' => 'int',
        'attachments_paths' => 'array',
    ];

    static $counter = 0;

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'tweet_id_str', 'id_str');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'tweet_id_str', 'id_str');
    }

    public function getAttachmentsAttribute()
    {
        if ($this->attachments_type)
        {
            return [
                'type' => $this->attachments_type,
                'paths' => $this->attachments_paths,
            ];
        }

        return [];
    }

    public function getMedia()
    {
        $tweet = $this->tweet;

        $tweetMedias = [];

        static::$counter = 0;

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

    public function getMediaPathInStorage($extension)
    {
        static::$counter++;

        return $this->getMediaDirPathInStorage() . $this->tweet_id_str . '_' . static::$counter . '.' . $extension;
    }

}
