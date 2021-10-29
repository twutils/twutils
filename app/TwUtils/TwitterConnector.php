<?php

namespace App\TwUtils;

use App\Models\SocialUser;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\TwUtils\Contracts\TwitterConnector;

class TwitterConnector implements TwitterConnector
{
    public function get(SocialUser $user)
    {
        if (app('env') === 'testing') {
            return dd('Error on binding testing twitter mock client');
        }

        $clientId = config('services.twitter.client_id');
        $clientSecret = config('services.twitter.client_secret');

        if ($user->hasWriteScope()) {
            $clientId = env('TWITTER_READ_WRITE_CLIENT_ID');
            $clientSecret = env('TWITTER_READ_WRITE_CLIENT_SECRET');
        }

        return new TwitterOAuth(
            $clientId,
            $clientSecret,
            $user->token,
            $user->token_secret
        );
    }
}
