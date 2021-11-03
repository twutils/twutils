<?php

namespace App\Models;

use App\Utils;
use App\Jobs\ProcessUploadJob;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\InteractsWithStorage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Upload extends Model
{
    use HasFactory, InteractsWithStorage;

    public const UPLOADS_DIR = 'tmp';

    protected $guarded = ['id'];

    protected $casts = [
        'id'             => 'int',
        'user_id'        => 'int',
        'filename'       => 'string',
        'original_name'  => 'string',
        'size'           => 'int',
        'purpose'        => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $upload) {
            dispatch(new ProcessUploadJob($upload));
        });

        static::deleting(function (self $upload) {
            if (static::getStorageDisk()->exists(static::UPLOADS_DIR.'/'.$upload->filename)) {
                static::getStorageDisk()->delete(static::UPLOADS_DIR.'/'.$upload->filename);
            }

            $upload->rawTweets()->getQuery()->delete();
        });
    }

    public function getSizeAttribute($value)
    {
        return Utils::humanize_bytes($value);
    }

    // Relations
    public function rawTweets() : HasMany
    {
        return $this->hasMany(RawTweet::class, 'upload_id', 'id');
    }

    public function rawTweetsFirst()
    {
        return $this->hasOne(RawTweet::class, 'upload_id', 'id')->orderBy('tweet_created_at', 'asc');
    }

    public function rawTweetsLast()
    {
        return $this->hasOne(RawTweet::class, 'upload_id', 'id')->orderBy('tweet_created_at', 'desc');
    }
}
