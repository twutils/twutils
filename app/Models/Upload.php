<?php

namespace App\Models;

use App\Jobs\ProcessUploadJob;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\InteractsWithStorage;
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
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $upload) {
            dispatch(new ProcessUploadJob($upload));
        });

        // static::deleted(function (self $upload) {
        //     if (static::getStorageDisk()->exists($upload->filename)) {
        //         static::getStorageDisk()->delete($upload->filename);
        //     }
        // });
    }

    // Relations
    public function rawTweets()
    {
        return $this->hasMany(RawTweet::class, 'upload_id', 'id');
    }
}
