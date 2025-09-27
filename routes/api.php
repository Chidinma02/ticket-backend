<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TicketController;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('users', UserController::class);

// Events

Route::post('/events/{id}/order', [OrderController::class, 'store']);
Route::apiResource('events', EventController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
});
// Orders
Route::apiResource('orders', OrderController::class);

// Tickets (optional)
Route::apiResource('tickets', TicketController::class);