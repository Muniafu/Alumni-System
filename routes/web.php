<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ProfileCompletionController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/complete', [ProfileCompletionController::class, 'show'])
        ->name('profile.complete');
    Route::post('/profile/complete', [ProfileCompletionController::class, 'store']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return view('dashboard', [
            'user' => $user
        ]);
    })->name('dashboard');
});