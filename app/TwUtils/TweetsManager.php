<?php

namespace App\TwUtils;

use Carbon\Carbon;
use App\Models\Tweet;
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

        $notFoundTweets = $tweets->pluck('id_str')->diff($foundTweets->pluck('id_str'));

        $foundTweets->map(function (Tweet $tweet) use ($tweets) {
            return static::updateTweetIfNeeded($tweet, $tweets->where('id_str', $tweet->id_str)->first());
        });

        $notFoundTweets->map(function ($tweetIdStr) use ($tweets) {
            return static::createTweet($tweets->where('id_str', $tweetIdStr)->first());
        });
    }

    public static function updateTweetIfNeeded($tweet, $mappedTweet)
    {
        $needUpdate = false;

        foreach ($mappedTweet as $key => $value) {
            if ($tweet->$key === $value) {
                continue;
            }
            $needUpdate = true;
            break;
        }

        if ($needUpdate) {
            $tweet->update($mappedTweet);
        }

        return $tweet;
    }

    public static function createTweet(array $tweet)
    {
        return Tweet::create($tweet);
    }

    public static function mapResponseToTweet(array $tweet): array
    {
        return [
            'id_str'                  => $tweet['id_str'],
            'extended_entities'       => $tweet['extended_entities'] ?? [],
            'entities'                => $tweet['entities'] ?? [],
            'text'                    => $tweet['full_text'],
            'lang'                    => $tweet['lang'],
            'retweet_count'           => $tweet['retweet_count'] ?? null,
            'favorite_count'          => isset($tweet['retweeted_status']) ? $tweet['retweeted_status']['favorite_count'] : $tweet['favorite_count'],
            'tweet_created_at'        => Carbon::createFromTimeString($tweet['created_at']),
            'tweep_id_str'            => $tweet['user']['id_str'],
            'in_reply_to_screen_name' => $tweet['in_reply_to_screen_name'] ?? null,
            'mentions'                => Str::limit(collect(Arr::get($tweet, 'entities.user_mentions', []))->implode('screen_name', ','), 251),
            'hashtags'                => Str::limit(collect(Arr::get($tweet, 'entities.hashtags', []))->implode('text', ','), 251),
            'is_quote_status'         => $tweet['is_quote_status'] ?? false,
            'quoted_status'           => isset($tweet['quoted_status']) ? $tweet['quoted_status'] : null,
            'quoted_status_permalink' => isset($tweet['quoted_status_permalink']) ? $tweet['quoted_status_permalink'] : null,
            'retweeted_status'        => isset($tweet['retweeted_status']) ? $tweet['retweeted_status'] : null,
        ];
    }
}
