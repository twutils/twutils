<?php

namespace App;

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

        static::deleted(function (self $tweet) {
            $tweep = Tweep::where('id_str', $tweet->tweep_id_str)->first();

            if (! $tweep)
            {
                return ;
            }

            $tweepOtherTweets = self::where('id_str', '!=', $tweet->id_str)
                            ->where('tweep_id_str', $tweep->id_str)->get()
                            ->concat(
                                Following::where('tweep_id_str', $tweep->id_str)->get()
                            )
                            ->concat(
                                Follower::where('tweep_id_str', $tweep->id_str)->get()
                            );

            if ($tweepOtherTweets->isEmpty()) {
                $tweep->delete();
            }
        });
    }

    public function tweep()
    {
        return $this->belongsTo(Tweep::class, 'tweep_id_str', 'id_str');
    }
}
