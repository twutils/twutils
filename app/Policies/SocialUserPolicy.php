<?php

namespace App\Policies;

use App\SocialUser;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocialUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the social user.
     *
     * @param \App\User       $user
     * @param \App\SocialUser $socialUser
     *
     * @return mixed
     */
    public function view(User $user, SocialUser $socialUser)
    {
        //
    }

    /**
     * Determine whether the user can create social users.
     *
     * @param \App\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the social user.
     *
     * @param \App\User       $user
     * @param \App\SocialUser $socialUser
     *
     * @return mixed
     */
    public function update(User $user, SocialUser $socialUser)
    {
        return $user->socialUsers->pluck('id')->contains($socialUser->id);
    }

    /**
     * Determine whether the user can delete the social user.
     *
     * @param \App\User       $user
     * @param \App\SocialUser $socialUser
     *
     * @return mixed
     */
    public function delete(User $user, SocialUser $socialUser)
    {
        //
    }

    /**
     * Determine whether the user can restore the social user.
     *
     * @param \App\User       $user
     * @param \App\SocialUser $socialUser
     *
     * @return mixed
     */
    public function restore(User $user, SocialUser $socialUser)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the social user.
     *
     * @param \App\User       $user
     * @param \App\SocialUser $socialUser
     *
     * @return mixed
     */
    public function forceDelete(User $user, SocialUser $socialUser)
    {
        //
    }
}
