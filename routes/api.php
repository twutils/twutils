<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/contact', 'WelcomeController@storeContact');
});

Route::middleware('auth:api', 'throttle:200,1')->group(function () {
    Route::get('tasks', 'Api\TasksController@index')->name('tasks');
    Route::get('tasks/likes', 'Api\TasksController@listLikesTasks')->name('tasks.listLikesTasks');
    Route::get('tasks/userTweets', 'Api\TasksController@listUserTweetsTasks')->name('tasks.listUserTweetsTasks');
    Route::get('tasks/{task}', 'Api\TasksController@show')->name('tasks.show');
    Route::get('tasks/{task}/data', 'Api\TasksController@getTaskData')->name('tasks.getTaskData');
    Route::get('tasks/{task}/managedTasks', 'Api\TasksController@getManagedTasks')->name('tasks.getManagedTasks');
    Route::delete('tasks/{task}', 'Api\TasksController@delete')->name('tasks.delete');

    Route::post('{any}/{task?}', 'Api\TasksController@create')->name('tasks.create');
    Route::get('{any}/{task?}', 'Api\TasksController@create')->name('tasks.getCreate');
});
