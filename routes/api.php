<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\SettingsApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route
Route::get('test', function () {
    return response()->json(['message' => 'API test route works!']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::patch('profile', [AuthController::class, 'updateProfile']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);

    // Post routes
    Route::apiResource('posts', PostApiController::class);

    // Settings routes
    Route::get('settings', [SettingsApiController::class, 'index']);
    Route::patch('settings', [SettingsApiController::class, 'update']);
    Route::get('platforms', [PlatformController::class, 'index']);
    Route::get('user-platforms', [PlatformController::class, 'userList']);
    Route::post('toggle-platforms', [PlatformController::class, 'update']);

});
