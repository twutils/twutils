<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTweet extends Pivot
{
    protected $table = 'task_tweet';

    protected $casts = [
        'task_id' => 'int',
    ];

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'tweet_id_str', 'id_str');
    }
}
