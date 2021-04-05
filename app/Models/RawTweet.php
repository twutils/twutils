<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RawTweet extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'upload_id'         => 'int',
        'id_str'            => 'string',
        'extended_entities' => 'array',
        'text'              => 'string',
        'retweet_count'     => 'int',
        'favorite_count'    => 'int',
        'tweet_created_at'  => 'datetime',
    ];

    // Relations
    public function upload()
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'id');
    }
}
