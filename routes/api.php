<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;


Route::get('/user', function () {
    return auth()->user();
});

Route::post('/posts/store', [PostController::class, 'store']);
Route::get('post/show', [PostController::class, 'show']);