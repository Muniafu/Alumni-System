<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/export-data', function (Request $request) {
        return response()->json($request->user()->exportData());
    });
});