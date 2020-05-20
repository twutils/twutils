<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTweet extends Pivot
{
    protected $table = 'task_tweet';

    protected $casts = [
        'attachments' => 'array',
    ];
}
