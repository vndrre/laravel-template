<?php

use App\Mail\Timetable;
use App\Console\Commands\TimetableNotification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mailable', function () {

    $data = app(TimetableNotification::class)->handle();
      
    return new Timetable($data);
});

