<?php

namespace App\TwUtils;

use App\Models\SocialUser;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterConnector implements ITwitterConnector
{
    public function get(SocialUser $user)
    {
        if (app('env') === 'testing') {
            return dump('Error on binding testing twitter mock client, remove this if you are willing to run unit tests on the live real twitter client..');
        }

        $clientId = config('services.twitter.client_id');
        $clientSecret = config('services.twitter.client_secret');

        if (in_array('write', $user->scope)) {
            $clientId = env('TWITTER_READ_WRITE_CLIENT_ID');
            $clientSecret = env('TWITTER_READ_WRITE_CLIENT_SECRET');
        }

        if (in_array('dm', $user->scope)) {
            $clientId = env('TWITTER_READ_WRITE_DM_CLIENT_ID');
            $clientSecret = env('TWITTER_READ_WRITE_DM_CLIENT_SECRET');
        }

        return new TwitterOAuth(
            $clientId,
            $clientSecret,
            $user->token,
            $user->token_secret
        );
    }
}
