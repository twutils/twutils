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

        static::updating(function (self $media) {
            if (! array_key_exists('status', $media->getDirty())) {
                return;
            }

            if ($media->status === static::STATUS_STARTED) {
                $media->started_at = now();
            }

            if ($media->status === static::STATUS_SUCCESS) {
                $media->success_at = now();
            }

            if ($media->status === static::STATUS_BROKEN) {
                $media->broken_at = now();
            }
        });

        static::saved(function (self $media) {
            dispatch(new ProcessMediaFileJob($media));
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
        return Storage::disk('tweetsMedia');
    }

    protected function getDownloader(): Downloader
    {
        return new $this->downloader($this);
    }
}
