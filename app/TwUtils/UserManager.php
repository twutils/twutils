<?php

namespace App\TwUtils;

use App\Models\User;
use App\Models\Export;
use App\Models\SocialUser;
use Illuminate\Support\Str;
use App\Jobs\FetchUserInfoJob;
use Laravel\Socialite\AbstractUser;
use Illuminate\Support\Facades\Auth;
use App\TwUtils\TwitterOperations\RevokeAccessOperation;

class UserManager
{
    public function loginSocialUser(AbstractUser $user, array $scopes = ['read'])
    {
        $socialUser = $this->createOrFindSocialUser($user, $scopes);

        $socialUser->fill(['scope' => $scopes] + $this->mapAbstractUserToSocialUser($user));

        $appUser = $this->createOrFindAppUser($socialUser);

        if (is_null($socialUser->email) && auth()->check() && $authUserEmail = auth()->user()->email) {
            $socialUser->email = $authUserEmail;
        }

        $appUser->email = $socialUser->email;
        $appUser->save();

        $socialUser->user_id = $appUser->id;
        $socialUser->save();

        $this->loginUser($appUser);
    }

    public function getClientData()
    {
        return [
            'baseUrl'    => url('/').'/',
            'apiBaseUrl' => url('/api').'/',
            'assetsUrl'  => asset('storage').'/',
            'returnUrl'  => session('returnUrl', ''),
            'locale'     => app()->getLocale(),
            'timeZone'   => config('app.timezone'),
            'langStore'  => __('messages'),
            'routes'     => ['twitter.rw.login' => route('twitter.rw.login')],
            'user'       => auth()->user() ? auth()->user()->load('socialUsers') : null,
            'exports'    => array_combine(Export::AVAILABLE_TYPES, Export::AVAILABLE_TYPES),
        ];
    }

    public function loginUser(User $user)
    {
        Auth::loginUsingId($user->id);
        session()->put('lastlogin_at', $user->lastlogin_at);
        $user->lastlogin_at = now();
        $user->save();
    }

    public function createOrFindAppUser(SocialUser $socialUser)
    {
        $socialUserId = $socialUser->social_user_id;

        $appUser = User::find($socialUser->user_id);

        if (! $appUser && auth()->check()) {
            $matchSocialUserId = auth()->user()->socialUsers
            ->filter(function ($socialUser) use ($socialUserId) {
                return $socialUser->social_user_id == $socialUserId;
            })->first();

            if ($matchSocialUserId) {
                $appUser = auth()->user();
            }
        }

        if (! $appUser) {
            $appUser = $this->createAppUserFromSocialUser($socialUser);
        }

        return $appUser;
    }

    public function refreshProfile(SocialUser $socialUser)
    {
        if ($this->shouldUpdateProfile($socialUser)) {
            $this->updateProfile($socialUser);
        }
    }

    public function shouldUpdateProfile(SocialUser $socialUser)
    {
        return $socialUser->updated_at->diffInSeconds($socialUser->created_at) == 0 || $socialUser->updated_at->diffInMinutes() > 15;
    }

    public function updateProfile(SocialUser $socialUser)
    {
        dispatch(new FetchUserInfoJob($socialUser));
    }

    public function revokeAccessToken(SocialUser $socialUser)
    {
        $fetchUserInfoOperation = new RevokeAccessOperation();

        $fetchUserInfoOperation->setSocialUser($socialUser)->dispatch();
    }

    public function resolveUser(User $appUser, string $scope)
    {
        return $appUser->socialUsers()->where('scope', 'like', "%{$scope}%")->first();
    }

    public function createAppUserFromSocialUser(SocialUser $socialUser): User
    {
        $appUser = new User();
        $appUser->name = $socialUser->name;

        if (User::where('email', $socialUser->email)->count() == 0) {
            $appUser->email = $socialUser->email;
        } else {
            $appUser->email = null;
        }

        $appUser->username = $socialUser->nickname;

        $appUser->api_token = Str::random(60);
        $appUser->save();

        return $appUser;
    }

    public function createOrFindSocialUser(AbstractUser $user, array $scopes): SocialUser
    {
        $socialUser = $this->findSocialUser($user, $scopes);

        if (is_null($socialUser)) {
            return $this->createSocialUser($user);
        }

        $alreadyDifferentScope = ! is_null($socialUser->scope) && $socialUser->scope !== $scopes;

        if ($alreadyDifferentScope) {
            $socialUser = $this->createSocialUser($user);
            $socialUser->user_id = $socialUser->user_id;
        }

        return $socialUser;
    }

    public function findSocialUser(AbstractUser $user, array $scopes)
    {
        $socialUsers = SocialUser::where('social_user_id', $user->getId())->get();

        return $socialUsers
        ->sortBy(function ($socialUser) {
            return strlen(implode($socialUser->scope));
        })
        ->filter(function ($socialUser) use ($scopes) {
            $foundScopes = [];
            foreach ($scopes as $scope) {
                if (in_array($scope, $socialUser->scope)) {
                    $foundScopes[] = $scope;
                }
            }

            return count($foundScopes) === count($scopes);
        })
        ->first();
    }

    public function createSocialUser(AbstractUser $user): SocialUser
    {
        $socialUser = new SocialUser();

        return $socialUser->fill($this->mapAbstractUserToSocialUser($user));
    }

    public function mapAbstractUserToSocialUser(AbstractUser $user): array
    {
        $map = $mapCopy = [
            'token'         => 'token',
            'token_secret'  => 'tokenSecret',

            'social_user_id' => 'getId()',
            'nickname'       => 'getNickname()',
            'name'           => 'getName()',
            'email'          => 'getEmail()',
            'avatar'         => 'getAvatar()',
        ];

        foreach ($map as $key => $value) {
            try {
                if (! Str::contains($value, '()')) {
                    $map[$key] = $user->{$value};
                } else {
                    $map[$key] = $user->{substr($value, 0, -2)}();
                }
            } catch (\Exception $e) {
            }
        }

        foreach ($map as $key => $value) {
            if ($value === $mapCopy[$key]) {
                $map[$key] = null;
            }
        }

        $map['avatar'] = AssetsManager::storeAvatar($map['avatar'], $map['social_user_id']);

        return $map;
    }
}
