<?php

namespace App;

use App\Jobs\ProcessMediaFileJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\TwUtils\Tweets\Media\Downloader;
use Illuminate\Filesystem\FilesystemAdapter;

class MediaFile extends Model
{
    protected $table = 'media_files';

    protected $guarded = ['id'];

    protected $fillable = ['media_id', 'downloader', 'name'];

    protected $casts = [
        'raw' => 'json',
    ];

    protected $appends = ['mediaPath'];

    public const STATUS_INITIAL = 'initial';
    public const STATUS_STARTED = 'started';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_BROKEN = 'broken';

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $mediaFile) {
            $mediaFile->status = 'initial';
        });

        static::updating(function (self $mediaFile) {
            if (! array_key_exists('status', $mediaFile->getDirty())) {
                return;
            }

            if ($mediaFile->status === static::STATUS_STARTED) {
                $mediaFile->started_at = now();
            }

            if ($mediaFile->status === static::STATUS_SUCCESS) {
                $mediaFile->success_at = now();
            }

            if ($mediaFile->status === static::STATUS_BROKEN) {
                $mediaFile->broken_at = now();
            }
        });

        static::saved(function (self $mediaFile) {
            dispatch(new ProcessMediaFileJob($mediaFile));
        });

        static::deleting(function (self $mediaFile) {
            if ($mediaFile->getStorageDisk()->exists($mediaFile->mediaPath)) {
                $mediaFile->getStorageDisk()->delete($mediaFile->mediaPath);
            }
        });
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function download(): self
    {
        return $this->getDownloader()->download();
    }

    public function getMediaPathAttribute()
    {
        return implode('', [$this->name, '_', $this->id, '.', $this->extension]);
    }

    public static function getStorageDisk(): FilesystemAdapter
    {
        return Storage::disk(config('filesystems.tweetsMedia'));
    }

    public static function getCacheStorageDisk(): FilesystemAdapter
    {
        return Storage::disk('tweetsMediaCache');
    }

    protected function getDownloader(): Downloader
    {
        return new $this->downloader($this);
    }
}
