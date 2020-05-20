<?php

namespace App\TwUtils;

use App\SocialUser;

interface ITwitterConnector
{
    public function get(SocialUser $socialUser);
}
