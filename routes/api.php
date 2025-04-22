<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;


Route::get('/user', function () {
    return auth()->user();
});

Route::post('/posts', [PostController::class, 'store']);