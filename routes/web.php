<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\QuickCreateController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::controller(UnitController::class)->group(function () {
        Route::get('/units', 'index');
        Route::get('/units/create', 'create');
        Route::post('/units', 'store');
        Route::get('/units/{id}/edit', 'edit');
        Route::put('/units/{id}', 'update');
        Route::delete('/units/{id}', 'destroy');
    });
    Route::controller(ProfileController::class)->group(function(){
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
    
    Route::resource('/products',ProductController::class);
    

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/quick-create', [QuickCreateController::class, 'store'])->name('quick-create.store');
});




Route::middleware(['auth'])->group(function () {
    Route::resource('/posts',PostController::class);
});


Route::get('/auth/google', [SocialController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

Route::get('/auth/facebook', [SocialController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialController::class, 'handleFacebookCallback']);

// Route::middleware('auth')->controller(CategoryController::class)->group(function () {
//     Route::get('categories', 'index')->name('category.index');
//     Route::get('categories/create', 'create')->name('category.create');
//     Route::post('categories', 'store')->name('category.store');
//     Route::get('categories/{id}/edit', 'edit')->name('category.edit');
// });





require __DIR__.'/auth.php';
