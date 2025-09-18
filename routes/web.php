<?php

use Illuminate\Support\Facades\Route;
use App\Support\Metrics;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/welcome', function () {
    return view('welcome');
});

// Expose Prometheus metrics
Route::get('/metrics', function () {
    return response(Metrics::render(), 200, [
        'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
    ]);
});
