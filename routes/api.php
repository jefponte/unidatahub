<?php

use App\Http\Controllers\Admin\BondController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;


Route::get('healthcheck', HealthController::class)->name('healthcheck');
Route::prefix('client')->group(function () {
    Route::post('/login', [AuthController::class, 'auth'])->name('client.login');
});
Route::post('authenticate', [AuthController::class, 'auth']);
Route::post('authenticate_md5', [AuthController::class, 'auth']);
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::get('/bond', [AuthController::class, 'bond'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'abilities:admin'])->prefix('admin')->group(function () {
    Route::get('/units', [AdminController::class, 'listUnits']);
    Route::get('/logins', [AdminController::class, 'listLogins']);
    Route::get('/users', [UserController::class, 'index']);
});

Route::middleware(['client'])->prefix('system')->group(function () {
    Route::get('/units', [AdminController::class, 'listUnits']);
    Route::get('/logins', [AdminController::class, 'listLogins']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/bonds', [BondController::class, 'index']);
});

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is working'
    ]);
});