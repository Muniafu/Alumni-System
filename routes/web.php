<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ProfileCompletionController;
use \App\Livewire\Dashboard;
use \App\Livewire\ProfileForm;
use App\Livewire\JobBoard;
use App\Livewire\DirectorySearch;
use App\Livewire\EventDetail;
use App\Livewire\EventForm;

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

Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/profile', ProfileForm::class)
    ->middleware(['auth', 'verified'])
    ->name('profile');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', ProfileForm::class)->name('profile');
    Route::get('/jobs', JobBoard::class)->name('jobs');
    Route::get('/directory', DirectorySearch::class)->name('directory');
});

Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('/alumni/locations', function () {
        return \App\Models\User::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('role', 'alumni')
            ->get(['name', 'location', 'latitude', 'longitude']);
    });
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Events
    Route::get('/events', \App\Livewire\EventsCalendar::class)->name('events.index');
    Route::get('/events/{event}/ical', function (App\Models\Event $event) {
        return response($event->toICalendar())
            ->header('Content-Type', 'text/calendar');
        })->name('events.ical');
    });