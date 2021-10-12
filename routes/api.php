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

Route::middleware('auth:api', 'throttle:2000,1')->group(function () {
    Route::get('tasks/{task}/data', 'Api\TasksController@getTaskData')->name('tasks.getTaskData');
});

Route::middleware('auth:api', 'throttle:100,1')->group(function () {
    Route::get('tasks', 'Api\TasksController@index')->name('tasks');
    Route::post('tasks/upload', 'Api\TasksController@uploadTask')->name('uploadTask');
    Route::get('tasks/uploads/{purpose?}', 'Api\TasksController@uploads')->name('uploads');
    Route::delete('tasks/uploads/{upload}', 'Api\TasksController@deleteUpload')->name('deleteUpload');

    Route::get('tasks/likes', 'Api\TasksController@listLikesTasks')->name('tasks.listLikesTasks');
    Route::get('tasks/userTweets', 'Api\TasksController@listUserTweetsTasks')->name('tasks.listUserTweetsTasks');
    Route::get('tasks/{task}', 'Api\TasksController@show')->name('tasks.show');
    Route::get('tasks/{task}/view', 'Api\TasksController@view')->name('tasks.view');
    Route::get('tasks/{task}/managedTasks', 'Api\TasksController@getManagedTasks')->name('tasks.getManagedTasks');
    Route::delete('tasks/{task}', 'Api\TasksController@delete')->name('tasks.delete');

    Route::post('exports/{task}/{exportType}', 'Api\ExportsController@add')->name('exports.add');
    Route::delete('exports/{export}', 'Api\ExportsController@delete')->name('exports.delete');

    Route::post('{any}/{task?}', 'Api\TasksController@create')->name('tasks.create');
    Route::get('{any}/{task?}', 'Api\TasksController@create')->name('tasks.getCreate');
});
