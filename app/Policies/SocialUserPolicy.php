<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SocialUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocialUserPolicy
{
    use HandlesAuthorization;

    public function update(User $user, SocialUser $socialUser)
    {
        return $user->socialUsers->pluck('id')->contains($socialUser->id);
    }
}
