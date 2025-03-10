<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RentalController;
use Illuminate\Http\Request;
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

Route::apiResource('users', AuthController::class);

Route::apiResource('cars', CarController::class)->middleware('auth:sanctum');

Route::apiResource('rentals', RentalController::class)->middleware('auth:sanctum');

Route::apiResource('payments', PaymentController::class)->middleware('auth:sanctum');