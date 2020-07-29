<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use App\SocialUser;
use App\Jobs\DeleteMeJob;
use App\TwUtils\UserManager;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['switchLang']);
    }

    public function index()
    {
        UserManager::refreshProfile(auth()->user()->load('socialUsers')->socialUsers[0]);

        return view('app');
    }

    public function revokeSocialUser(Request $request, SocialUser $socialUser)
    {
        $this->authorize('update', $socialUser);

        UserManager::revokeAccessToken($socialUser);

        return redirect()->back();
    }

    public function switchLang()
    {
        if (! in_array(request()->segment(1), ['en', 'ar'])) {
            return redirect()->route('welcome');
        }

        if (request()->segment(1)) {
            Session::put('locale', request()->segment(1));
            app()->setLocale(request()->segment(1));
        }

        if (auth()->guest() && session('url.intended')) {
            return redirect()->intended();
        }

        $previousRouteName = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();

        if (request()->has('returnUrl')) {
            session()->flash('returnUrl', request()->get('returnUrl'));
        }

        // This way, the selected language will remain in the url. Better URLs.
        if ($previousRouteName == 'welcome' || $previousRouteName == 'switchLang') {
            return view('welcome');
        }

        return redirect()->back();
    }

    public function profile()
    {
        $user = Auth::user();

        $lastLogin = session()->get('lastlogin_at');

        if (is_null($lastLogin)) {
            $lastLogin = $user->lastlogin_at;
        }

        return view('profile', ['user' => $user, 'lastLogin' => $lastLogin]);
    }

    public function deleteMe(Request $request)
    {
        $request->validate(
            [
                'day'    => 'nullable|integer|min:0',
                'hour'   => 'nullable|integer|min:0',
                'minute' => 'nullable|integer|min:0',
            ]
        );

        $user = $request->user();

        if ($user->remove_at !== null) {
            session()->flash('message', ['type' => 'info', 'message' => trans('messages.deleteMe_pending')]);

            return redirect()->back();
        }

        $deleteDate = now()
                    ->addDays(request()->get('day') ?? 0)
                    ->addHours(request()->get('hour') ?? 0)
                    ->addMinutes(request()->get('minute') ?? 0);

        $user->remove_at = $deleteDate;
        $user->save();

        dispatch(new DeleteMeJob($user))->delay($deleteDate);

        return redirect()->back();
    }

    public function cancelDeleteMe(Request $request)
    {
        $user = $request->user();

        if ($user->remove_at === null) {
            return redirect()->back();
        }

        $user->remove_at = null;
        $user->save();

        session()->flash('message', ['type' => 'success', 'message' => trans('messages.deleteMe_canceled')]);

        return redirect()->back();
    }
}
