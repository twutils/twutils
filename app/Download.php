<?php

namespace App;

use App\Jobs\ProcessDownloadJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Download extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'task_id' => 'int',
        'started_at' => 'datetime',
        'success_at' => 'datetime',
    ];

    public const STATUS_INITIAL = 'initial';
    public const STATUS_STARTED = 'started';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_BROKEN = 'broken';

    public const TYPE_HTML = 'html';
    public const TYPE_EXCEL = 'excel';
    public const TYPE_HTMLENTITIES = 'htmlEntities';

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $download) {
            $download->status = 'initial';
        });

        static::updating(function (self $download) {
            if (! array_key_exists('status', $download->getDirty())) {
                return;
            }

            if ($download->status === static::STATUS_STARTED) {
                $download->started_at = now();
            }

            if ($download->status === static::STATUS_SUCCESS) {
                $download->success_at = now();
            }

            if ($download->status === static::STATUS_BROKEN) {
                $download->broken_at = now();
            }
        });

        static::saved(function (self $download) {
            dispatch(new ProcessDownloadJob($download));
        });
    }

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function toResponse()
    {
        return Storage::disk(config('filesystems.cloud'))->response($this->id);
    }
}
