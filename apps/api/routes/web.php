<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', function () {
    return view('swagger');
})->name('swagger.ui');

Route::get('/docs/openapi.json', function () {
    $path = base_path('openapi.json');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/json',
        'Cache-Control' => 'no-store, private',
    ]);
})->name('swagger.openapi');
