<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = "media";

    protected $guarded = ['id'];

    protected $fillable = ['tweet_id'];

    protected static function boot()
    {
        static::saved(function (self $media) {
            dispatch(new ProcessMediaJob($media));
        });
    }
}
