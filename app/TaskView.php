<?php

namespace App;

use App\Jobs\ProcessExportJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;

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
