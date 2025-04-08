<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Public
Route::middleware('throttle:10,1')->group(function () {
    // Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);

    // User
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });

    // Admin
    Route::get('/admin-only', function () {
        return response()->json([
            'message' => 'You are an admin'
        ]);
    })->middleware('role:admin');
});
