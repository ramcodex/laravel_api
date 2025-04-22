<?php

//1. Basic Routes
Route::get('/home', function () {
    return view('home');
});




//2. Route with Parameters
Route::get('/user/{id}', function ($id) {
    return "User ID: " . $id;
});

//3. Named Routes
Route::get('/profile', function () {
    // ...
})->name('profile');

//4. Route Groups
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        // Authenticated user dashboard
    });
});

//5. Prefix Routes
Route::prefix('admin')->group(function () {
    Route::get('/users', function () {
        return "Admin Users";
    });
});

//6. Combine both
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/users', function () {
        return "Only authenticated admins can see this.";
    });
});

//7. Route with Middleware
Route::get('/settings', function () {
    // Protected Settings
})->middleware('auth');

//8. Route Resource
Route::resource('posts', PostController::class);


//9. Route API Resource
Route::apiResource('posts', PostController::class);

//10. Route Fallback
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

//11. Route Model Binding
Route::get('/post/{post}', function (Post $post) {
    return $post;
});

//-------------------------------------------------------------------

//1. Basic Routes
Route::get('/home', [HomeController::class, 'index']);


//2. Route with Parameters
Route::get('/user/{id}', [UserController::class, 'show']);


//3. Named Routes
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');


//4. Route Groups
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});


//5. Prefix Routes
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'users']);
});


//6. Combine both
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/users', [AdminController::class, 'adminUsers']);
});


//7. Route with Middleware
Route::get('/settings', [SettingsController::class, 'index'])->middleware('auth');


//8. Route Resource
Route::resource('posts', PostController::class);


//9. Route API Resource
Route::apiResource('posts', PostController::class);


//10. Route Fallback
Route::fallback([FallbackController::class, 'handle']);


//11. Route Model Binding
Route::get('/post/{post}', [PostController::class, 'show']);
