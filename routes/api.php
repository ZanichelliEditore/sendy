<?php

use Illuminate\Support\Facades\Route;



Route::post('/v1/emails', 'EmailController@send');
