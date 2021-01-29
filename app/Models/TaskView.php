<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskView extends Model
{
    protected $table = 'task_views';

    protected $guarded = ['id'];

    protected $casts = [
        'task_id' => 'int',
        'count' => 'int',
        'tweets_text_only' => 'int',
        'tweets_with_photos' => 'int',
        'tweets_with_videos' => 'int',
        'tweets_with_gifs' => 'int',

        'months' => 'json',
    ];

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }
}
