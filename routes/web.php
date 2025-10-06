<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'UpdatePrice API is running',
        'timestamp' => now()->toIso8601String(),
        'php_version' => phpversion(),
        'laravel_version' => app()->version()
    ]);
});

Route::get('/health', function () {
    return response()->json(['status' => 'healthy']);
});

// Route::get('/register', [ApiController::class, 'register' ]);