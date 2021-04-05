<?php

namespace App\TwUtils;

use App\Tweet;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Upload;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class RawTweetsManager
{
    public function create(UploadedFile $uploadedFile, User $user) : Upload
    {
        $fileName = Str::uuid().'.js';

        $uploadedFile->storeAs(Upload::UPLOADS_DIR, $fileName, ['disk' => config('filesystems.cloud')]);

        $upload = Upload::create([
            'filename'      => $fileName,
            'original_name' => $uploadedFile->getClientOriginalName(),
        ]);

        return $upload;
    }

    public function insertOrUpdateMultipleTweets(Collection $tweets)
    {
        $tweets = $tweets->unique('id_str')->map(function ($tweet) {
            $tweet = (array) json_decode(json_encode($tweet), true);

            return static::mapResponseToTweet($tweet);
        });

        $notFound = $tweets->pluck('id_str')->diff($foundTweetsIds);

        $foundTweets->map(function (Tweet $tweet) use ($tweets) {
            return static::updateTweetIfNeeded($tweet, $tweets->where('id_str', $tweet->id_str)->first());
        });

        $notFound->map(function ($tweetIdStr) use ($tweets) {
            return static::createTweet($tweets->where('id_str', $tweetIdStr)->first());
        });
    }

    public function updateTweetIfNeeded($tweet, $mappedTweet)
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

    public function createTweet(array $tweet)
    {
        return Tweet::create($tweet);
    }

    public function mapResponseToTweet(array $tweet): array
    {
        return [
            'id_str'                  => $tweet['id_str'],
            'extended_entities'       => $tweet['extended_entities'] ?? [],
            'text'                    => $tweet['full_text'],
            'retweet_count'           => $tweet['retweet_count'] ?? null,
            'favorite_count'          => $tweet['favorite_count'] ?? null,
            'tweet_created_at'        => Carbon::createFromTimeString($tweet['created_at']),
        ];
    }
}
