<?php

namespace App;

use App\Jobs\ProcessExportJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;

class Export extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'task_id' => 'int',
        'started_at' => 'datetime',
        'success_at' => 'datetime',
        'progress' => 'int',
        'progress_end' => 'int',
        'list_size' => 'int',
    ];

    protected $hidden = [
        'exception',
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

    public const AVAILABLE_TYPES = [
        self::TYPE_HTML,
        self::TYPE_EXCEL,
        self::TYPE_HTMLENTITIES,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $export) {
            $export->status = static::STATUS_INITIAL;
        });

        static::created(function (self $export) {
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
            $dirtyKeys = array_keys($export->getDirty());

            if (
                empty($dirtyKeys) ||
                in_array('progress', $dirtyKeys) ||
                in_array('progress_end', $dirtyKeys)
            )
            {
                return ;
            }

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
        $adapter = static::getStorageDisk()->getDriver()->getAdapter();

        if ($adapter instanceof AwsS3Adapter)
        {
            return redirect()
                    ->away(
                        static::getStorageDisk()->temporaryUrl(
                            $this->id,
                            now()->addMinutes(1),
                            [
                                'ResponseContentDisposition' => 'attachment; filename=' . $this->filename,
                            ]
                        )
                    );
        }

        return static::getStorageDisk()->response($this->id, $this->filename);
    }

    public static function getStorageDisk(): FilesystemAdapter
    {
        return Storage::disk(static::getStorageDiskName());
    }

    public static function getStorageDiskName(): string
    {
        return config('filesystems.cloud');
    }
}
