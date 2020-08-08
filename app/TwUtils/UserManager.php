<?php

namespace App\TwUtils;

use App\User;
use App\SocialUser;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Illuminate\Support\Facades\Auth;
use App\TwUtils\TwitterOperations\RevokeAccessOperation;
use App\TwUtils\TwitterOperations\FetchUserInfoOperation;

class UserManager
{
    public static function loginSocialUser(AbstractUser $user, array $scopes = ['read'])
    {
        $socialUser = static::createOrFindSocialUser($user, $scopes);

        $alreadyDifferentScope = ! is_null($socialUser->scope) && $socialUser->scope !== $scopes;

        if ($alreadyDifferentScope) {
            $userId = $socialUser->user_id;
            $socialUser = static::createSocialUser($user);
            $socialUser->user_id = $userId;
        }

        $socialUser->fill(['scope' => $scopes] + static::mapAbstractUserToSocialUser($user));

        $appUser = static::createOrFindAppUser($socialUser);

        if (is_null($socialUser->email) && auth()->check() && $authUserEmail = auth()->user()->email) {
            $socialUser->email = $authUserEmail;
        }

        $appUser->email = $socialUser->email;
        $appUser->save();

        $socialUser->user_id = $appUser->id;
        $socialUser->save();

        static::loginUser($appUser);
    }

    public static function getClientData()
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
        ];
    }

    public static function loginUser(User $user)
    {
        Auth::loginUsingId($user->id);
        session()->put('lastlogin_at', $user->lastlogin_at);
        $user->lastlogin_at = now();
        $user->save();
    }

    public static function createOrFindAppUser(SocialUser $socialUser)
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
            $appUser = static::createAppUserFromSocialUser($socialUser);
        }

        return $appUser;
    }

    public static function refreshProfile(SocialUser $socialUser)
    {
        if (static::shouldUpdateProfile($socialUser)) {
            static::updateProfile($socialUser);
        }
    }

    public static function shouldUpdateProfile(SocialUser $socialUser)
    {
        return $socialUser->updated_at->diffInSeconds($socialUser->created_at) == 0 || $socialUser->updated_at->diffInMinutes() > 15;
    }

    public static function updateProfile(SocialUser $socialUser)
    {
        $fetchUserInfoOperation = new FetchUserInfoOperation();

        $fetchUserInfoOperation->setSocialUser($socialUser)->dispatch();
    }

    public static function revokeAccessToken(SocialUser $socialUser)
    {
        $fetchUserInfoOperation = new RevokeAccessOperation();

        $fetchUserInfoOperation->setSocialUser($socialUser)->dispatch();
    }

    public static function resolveUser(User $appUser, string $scope)
    {
        return $appUser->socialUsers()->where('scope', 'like', "%{$scope}%")->first();
    }

    public static function createAppUserFromSocialUser(SocialUser $socialUser): User
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

    public static function createOrFindSocialUser(AbstractUser $user, array $scopes): SocialUser
    {
        $socialUser = static::findSocialUser($user, $scopes);

        if (is_null($socialUser)) {
            return static::createSocialUser($user);
        }

        return $socialUser;
    }

    public static function findSocialUser(AbstractUser $user, array $scopes)
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

    public static function createSocialUser(AbstractUser $user): SocialUser
    {
        $socialUser = new SocialUser();

        return $socialUser->fill(static::mapAbstractUserToSocialUser($user));
    }

    public static function mapAbstractUserToSocialUser(AbstractUser $user): array
    {
        $map = $mapCopy = [
            'token'         => 'token',
            'token_secret'  => 'tokenSecret',
            'refresh_token' => 'refreshToken',
            'expires_in'    => 'expiresIn',

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

        $map['nickname'] = $map['nickname'];
        $map['avatar'] = AssetsManager::storeAvatar($map['avatar'], $map['social_user_id']);

        return $map;
    }
}
