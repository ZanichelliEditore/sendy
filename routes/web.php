<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect('failedJobs');
});

Route::get('/failedJobs', function () {
    return view('failedJobs');
})->name('failedJobs');


Route::get('/jobs','JobController@getFile')->name('jobs');