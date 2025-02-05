<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\RentalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class,'logout']);
    Route::post('cars', [CarController::class, 'store']);
    Route::get('cars', [CarController::class, 'index']);
    Route::get('cars/{id}', [CarController::class, 'show']);
    Route::delete('cars/{id}', [CarController::class, 'destroy']);
    Route::get('rentals', [RentalController::class, 'index']);
    Route::get('rentals/{id}', [RentalController::class, 'show']);
    Route::post('cars/{id}/rentals', [RentalController::class, 'store']);
    Route::post('rentals/{id}', [RentalController::class, 'returnCar']);
    Route::get("stat",[RentalController::class,'getStatistics']);
});
