<?php

namespace App\TwUtils;

use App\Models\SocialUser;

interface ITwitterConnector
{
    public function get(SocialUser $socialUser);
}
