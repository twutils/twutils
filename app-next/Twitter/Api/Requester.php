<?php

namespace AppNext\Twitter\Api;

use App\Utils;
use App\Models\SocialUser;
use Atymic\Twitter\Facade\Twitter;
use Atymic\Twitter\ApiV1\Service\Twitter as ServiceTwitter;

class Requester
{
    protected ServiceTwitter $twitterV1;

    public function __construct(
        protected SocialUser $socialUser,
    ) {
        if ($socialUser->hasWriteScope()) {
            Utils::setup_twitter_config_for_read_write();
        }

        $this->twitterV1 = Twitter::usingCredentials(
                $socialUser->token,
                $socialUser->token_secret,
                config('services.twitter.client_id'),
                config('services.twitter.client_secret'),
            )
            ->forApiV1();
    }

    public static function for(SocialUser $socialUser)
    {
        return new static($socialUser);
    }

    public function destroyFavorite($parameters = [])
    {
        return $this->twitterV1->destroyFavorite($parameters);
    }

    public function destroyTweet($id, $parameters = [])
    {
        return $this->twitterV1->destroyTweet($id, $parameters);
    }
}
