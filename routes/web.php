<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'idp'], function () {

    Route::get('/unauthorized', function () {
        return view('unauthorized');
    })->name('unauthorized');

    Route::get('logout',  'LoginController@logout');

    Route::group(['middleware' => 'check.role'], function () {

        Route::get('/', function () {
            return redirect('failedJobs');
        });

        Route::get('/failedJobs', function () {
            return view('failedJobs');
        })->name('failedJobs');

        Route::get('/jobs', function () {
            return view('jobs');
        })->name('jobs');
    });
});
