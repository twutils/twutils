<?php

namespace App\TwUtils;

use App\Tweet;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class TweetsManager
{
    public static function insertOrUpdateMultipleTweets(Collection $tweets)
    {
        $tweets = $tweets->unique('id_str')->map(function ($tweet) {
            $tweet = (array) json_decode(json_encode($tweet), true);

            return static::mapResponseToTweet($tweet);
        });

        $foundTweets = Tweet::whereIn('id_str', $tweets->pluck('id_str'))->get();
        $foundTweetsIds = $foundTweets->pluck('id_str');

        $notFound = $tweets->pluck('id_str')->diff($foundTweetsIds);
        
        $foundTweets->map(function (Tweet $tweep) use ($tweets) {
            return static::updateTweetIfNeeded($tweep, $tweets->where('id_str', $tweep->id_str)->first());
        });

        $notFound->map(function ($tweepIdStr) use ($tweets) {
            return static::createTweet($tweets->where('id_str', $tweepIdStr)->first());
        });
    }

    public static function updateTweetIfNeeded($tweep, $mappedTweet)
    {
        $needUpdate = false;

        foreach ($mappedTweet as $key => $value) {
            if ($tweep->$key === $value) {
                continue;
            }
            $needUpdate = true;
            break;
        }

        if ($needUpdate) {
            $tweep->update($mappedTweet);
        }

        return $tweep;
    }

    public static function createTweet(array $tweep)
    {
        return Tweet::create($tweep);
    }

    public static function mapResponseToTweet(array $tweet): array
    {
        return [
            'id_str'                  => $tweet['id_str'],
            'extended_entities'       => json_encode($tweet['extended_entities'] ?? []),
            'text'                    => $tweet['full_text'],
            'lang'                    => $tweet['lang'],
            'retweet_count'           => $tweet['retweet_count'] ?? null,
            'favorite_count'          => isset($tweet['retweeted_status']) ? $tweet['retweeted_status']['favorite_count'] : $tweet['favorite_count'],
            'tweet_created_at'        => Carbon::createFromTimeString($tweet['created_at']),
            'tweep_id_str'            => $tweet['user']['id_str'],
            'in_reply_to_screen_name' => $tweet['in_reply_to_screen_name'] ?? null,
            'mentions'                => Str::limit(collect(Arr::get($tweet, 'entities.user_mentions', []))->implode('screen_name', ','), 254),
            'hashtags'                => Str::limit(collect(Arr::get($tweet, 'entities.hashtags', []))->implode('text', ','), 254),
            'is_quote_status'         => $tweet['is_quote_status'] ?? false,
            'quoted_status'           => isset($tweet['quoted_status']) ? json_encode($tweet['quoted_status']) : null,
            'quoted_status_permalink' => isset($tweet['quoted_status_permalink']) ? json_encode($tweet['quoted_status_permalink']) : null,
            'retweeted_status'        => isset($tweet['retweeted_status']) ? json_encode($tweet['retweeted_status']) : null,
        ];
    }
}
