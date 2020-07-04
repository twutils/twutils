<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if (app('env') != 'production' && file_exists($tempRoutes = base_path('temp/tempRoutes.php'))) {
    include $tempRoutes;
}

Route::get('/', 'WelcomeController@index')->name('welcome');
Route::post('/', 'WelcomeController@index');

Route::get('/about', 'WelcomeController@about')->name('about');

Route::get('/contact', 'WelcomeController@contact')->name('contact');

Route::get('/privacy', 'WelcomeController@privacy')->name('privacy');

Route::get('login', 'LoginController@showLoginForm')->name('login');

Route::post('logout', 'LoginController@logout')->name('logout');

Route::get('login/twitter/rw', 'LoginController@redirectToProviderWithReadWrite')->name('twitter.rw.login');
Route::get('login/twitter/rw/callback', 'LoginController@handleProviderCallbackWithReadWrite')->name('twitter.rw.callback');

Route::get('/app', 'AppController@index')->name('app');

Route::get('login/twitter', 'LoginController@redirectToProvider')->name('twitter.login');

Route::get('login/twitter/callback', 'LoginController@handleProviderCallback')->name('twitter.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('profile', 'AppController@profile')->name('profile');
    Route::post('revokeSocialUser/{socialUser}', 'AppController@revokeSocialUser')->name('revokeSocialUser');
    Route::post('deleteMe', 'AppController@deleteMe')->name('deleteMe');
    Route::get('cancelDeleteMe', 'AppController@cancelDeleteMe')->name('cancelDeleteMe');
    Route::get('task/{task}/download/{download}', 'DownloadsController@show')->name('downloads.show');
});

Route::get('/{lang?}', 'AppController@switchLang')->name('switchLang');
