<?php

namespace AppNext\Twitter;

use App\Models\SocialUser;
use App\Utils;
use Atymic\Twitter\ApiV1\Service\Twitter as ServiceTwitter;
use Atymic\Twitter\Facade\Twitter;

class Requester
{
    /** @var \Atymic\Twitter\ApiV1\Service\Twitter */
    protected ServiceTwitter $twitterV1; 

    public function __construct(
        protected SocialUser $socialUser,
    )
    {

        if (in_array('write', $socialUser->scope))
        {
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