<?php

namespace App;

use App\Jobs\ProcessDownloadJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

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

    public const EXTENSIONS = [
        self::TYPE_EXCEL        => 'xlsx',
        self::TYPE_HTML         => 'zip',
        self::TYPE_HTMLENTITIES => 'zip',
    ];

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
                $download->filename = implode('', [

                    $download->task->socialUser->nickname,
                    '-',
                    $download->task->shortName,
                    '-',
                    $download->created_at->format('m-d-Y_hia'),
                    '.',
                    static::EXTENSIONS[$download->type],
                ]);

                $download->success_at = now();
            }

            if ($download->status === static::STATUS_BROKEN) {
                $download->broken_at = now();
            }
        });

        static::saved(function (self $download) {
            dispatch(new ProcessDownloadJob($download));
        });

        static::deleted(function (self $download) {
            if ( static::getStorageDisk()->exists($download->id))
            {
                static::getStorageDisk()->delete($download->id);
            }
        });
    }

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function toResponse()
    {
        return static::getStorageDisk()->response($this->id, $this->filename);
    }

    public static function getStorageDisk(): FilesystemAdapter
    {
        return Storage::disk(config('filesystems.cloud'));
    }
}
