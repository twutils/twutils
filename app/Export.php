<?php

namespace App;

use App\Jobs\ProcessExportJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class Export extends Model
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

        static::creating(function (self $export) {
            $export->status = 'initial';
        });

        static::updating(function (self $export) {
            if (! array_key_exists('status', $export->getDirty())) {
                return;
            }

            if ($export->status === static::STATUS_STARTED) {
                $export->started_at = now();
            }

            if ($export->status === static::STATUS_SUCCESS) {
                $export->filename = implode('', [

                    $export->task->socialUser->nickname,
                    '-',
                    $export->task->shortName,
                    '-',
                    $export->created_at->format('Y-m-d_hia'),
                    '.',
                    static::EXTENSIONS[$export->type],
                ]);

                $export->size = static::getStorageDisk()->size($export->id);
                $export->success_at = now();
            }

            if ($export->status === static::STATUS_BROKEN) {
                $export->broken_at = now();
            }
        });

        static::saved(function (self $export) {
            dispatch(new ProcessExportJob($export));
        });

        static::deleted(function (self $export) {
            if ( static::getStorageDisk()->exists($export->id))
            {
                static::getStorageDisk()->delete($export->id);
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
