<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\AuthController;



Route::get('/user', function () {
    return auth()->user();
});

Route::post('/posts/store', [PostController::class, 'store']);
Route::get('post/show', [PostController::class, 'show']);

Route::put('post/update/{id}', [PostController::class, 'update']);
Route::post('/login', [AuthController::class, 'login']);

//Adding on main branch.
