<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['client_credentials']], function () {
    Route::post('/v1/emails', 'EmailController@send');
});

Route::prefix('failedJobs')->group(function () {
    Route::get('/', 'FailedJobController@getList');
    Route::delete('{id}', 'FailedJobController@destroy');
    Route::delete('all', 'FailedJobController@destroyAll');
    Route::get('retry/{id}', 'FailedJobController@retryJob');
    Route::get('retry/all', 'FailedJobController@retryAll');
});

Route::prefix('jobs')->group(function () {
    Route::get('/', 'JobController@getFile');
});