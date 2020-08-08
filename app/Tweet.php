<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    protected $guarded = ['id'];
    protected $dates = [
        'created_at',
        'updated_at',
        'removed',
        'tweet_created_at',
    ];
    protected $casts = [
        'attachments'             => 'array',
        'extended_entities'       => 'array',
        'quoted_status'           => 'array',
        'quoted_status_permalink' => 'array',
        'retweeted_status'        => 'array',
    ];
    protected $with = ['tweep'];

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $tweet) {
            $tweet->initMedia();
        });

        static::deleted(function (self $tweet) {
            $tweet->media->map->delete();
        });
    }

    public function tweep()
    {
        return $this->belongsTo(Tweep::class, 'tweep_id_str', 'id_str');
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function initMedia()
    {
        $medias = Arr::get($this, 'extended_entities.media', []);

        foreach ($medias as $media) {
            Media::create(['tweet_id' => $this->id, 'raw' => $media]);
        }
    }
}
