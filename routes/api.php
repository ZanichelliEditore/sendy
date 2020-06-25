<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['client_credentials']], function () {
    Route::post('/v1/emails', 'EmailController@send');
});

Route::prefix('failedJobs')->group(function () {
    Route::get('/', 'FailedJobController@getList');
    Route::delete('all', 'FailedJobController@destroyAll');
    Route::delete('{id}', 'FailedJobController@destroy');
    Route::get('retry/all', 'FailedJobController@retryAll');
    Route::get('retry/{id}', 'FailedJobController@retryJob');
});

Route::prefix('jobs')->group(function () {
    Route::get('/', 'JobController@getFile');
    Route::get('clean/log', 'JobController@deleteLogs');
    Route::get('clean/access-token', 'JobController@deleteTokens');
});

Route::post('/logout-idp', 'LoginController@logoutIdp')->name('logoutIdp');