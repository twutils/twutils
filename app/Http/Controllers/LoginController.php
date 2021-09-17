<?php

namespace App\Http\Controllers;

use App\TwUtils\UserManager;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/app';

    public function __construct(
        protected UserManager $userManager
    ) {
        $this->middleware('guest')->only('showLoginForm');
    }

    public function showLoginForm()
    {
        return view('login');
    }

    public function redirectToProvider()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        if ($request->has('denied')) {
            session()->flash('message', ['type' => 'danger', 'message' => trans('messages.socialauth_canceled')]);

            return redirect()->route(auth()->check() ? 'app' : 'welcome');
        }

        $user = Socialite::driver('twitter')->user();

        $this->userManager->loginSocialUser($user);

        $socialUser = $this->userManager->findSocialUser($user, ['read', 'write']);

        if (! is_null($socialUser) && $socialUser->token !== '') {
            $this->userManager->loginUser($socialUser->user);

            return redirect()->route('twitter.rw.login');
        }

        return redirect()->route('app');
    }

    public function redirectToProviderWithReadWrite()
    {
        $this->setupTwitterConfigForReadWrite();

        return Socialite::driver('twitter')->redirect();
    }

    public function handleProviderCallbackWithReadWrite(Request $request)
    {
        if ($request->has('denied')) {
            session()->flash('message', ['type' => 'danger', 'message' => trans('messages.socialauth_canceled')]);

            return redirect()->route(auth()->check() ? 'app' : 'welcome');
        }

        $this->setupTwitterConfigForReadWrite();

        $user = Socialite::driver('twitter')->user();

        $this->userManager->loginSocialUser($user, ['read', 'write']);

        return redirect()->route('app');
    }

    protected function setupTwitterConfigForReadWrite()
    {
        if (app()->isProduction()) {
            config()->set('services.twitter.redirect', env('TWITTER_REDIRECT_READ_WRITE'));
        } else {
            config()->set('services.twitter.redirect', sprintf(env('TWITTER_REDIRECT_READ_WRITE'), env('APP_PORT')));
        }

        config()->set('services.twitter.client_id', env('TWITTER_READ_WRITE_CLIENT_ID'));
        config()->set('services.twitter.client_secret', env('TWITTER_READ_WRITE_CLIENT_SECRET'));
    }
}
