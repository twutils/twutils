<?php

namespace App\TwUtils\Contracts;

use App\Models\SocialUser;

interface TwitterConnector
{
    public function get(SocialUser $socialUser);
}
