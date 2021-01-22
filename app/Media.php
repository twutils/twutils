<?php

namespace App;

use App\Jobs\ProcessMediaJob;
use App\TwUtils\AssetsManager;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $guarded = ['id'];

    protected $fillable = ['tweet_id', 'raw'];

    protected $casts = [
        'raw' => 'json',
    ];

    public const TYPE_PHOTO = 'photo';
    public const TYPE_VIDEO = 'video';
    public const TYPE_ANIMATED_GIF = 'animated_gif';

    public const STATUS_INITIAL = 'initial';
    public const STATUS_STARTED = 'started';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_BROKEN = 'broken';

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $media) {
            $media->status = 'initial';
            $media->type = $media->raw['type'];
        });

        static::created(function (self $media) {
            $media->initMediaFiles();
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
            dispatch(new ProcessMediaJob($media));
        });

        static::deleting(function (self $media) {
            $media->mediaFiles->map->delete();
        });
    }

    public function initMediaFiles()
    {
        $counter = 1;

        collect(AssetsManager::getMediaDownloaders($this))
        ->map(function ($downloader) use (&$counter) {
            // TODO: Optimize loading "this->tweet"
            MediaFile::create(['downloader' => $downloader, 'media_id' => $this->id, 'name' => $this->tweet->id_str]);
        });
    }

    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class);
    }

    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }
}
