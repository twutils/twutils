<?php

namespace App;

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

    public function getMediaDirPathInStorage()
    {
        return $this->task_id . '/';
    }

    public function getMediaPathInStorage()
    {
        return $this->getMediaDirPathInStorage() . $this->tweet_id_str . '_';
    }

}
