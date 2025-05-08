<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ProfileCompletionController;

// Public routes
Route::view('/', 'welcome');

// Authentication routes
require __DIR__.'/auth.php';

// Socialite routes
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    
    // Profile
    Route::get('/profile', \App\Livewire\ProfileForm::class)->name('profile');
    
    // Job Board
    Route::get('/jobs', \App\Livewire\JobBoard::class)->name('jobs');
    
    // Directory
    Route::get('/directory', \App\Livewire\DirectorySearch::class)->name('directory');
    
    // Events
    Route::get('/events', \App\Livewire\EventsCalendar::class)->name('events.index');
    Route::get('/events/{event}/ical', function (App\Models\Event $event) {
        return response($event->toICalendar())
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename="'.Str::slug($event->title).'.ics"');
    })->name('events.ical');

    // Profile Completion
    Route::get('/profile/complete', [ProfileCompletionController::class, 'show'])
        ->name('profile.complete');
    Route::post('/profile/complete', [ProfileCompletionController::class, 'store']);
});

// API Routes
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('/alumni/locations', function () {
        return \App\Models\User::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('role', 'alumni')
            ->get(['name', 'location', 'latitude', 'longitude']);
    });
});