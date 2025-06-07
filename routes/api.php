<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ApiDocumentationController;
use Illuminate\Support\Facades\Route;

// Welcome route
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to Car Rental API']);
});

// API Documentation
Route::get('/docs', [ApiDocumentationController::class, 'index']);

// Public car service routes
Route::get('/cars/service-shops', [CarController::class, 'getServiceShopNames']);
Route::get('/cars/service-history', [CarController::class, 'getServiceHistory']);

// Auth routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Car routes
    Route::apiResource('cars', CarController::class);
    Route::get('/cars/{carId}/rentals', [RentalController::class, 'getRentalsByCar']);
    
    // Rental routes
    Route::apiResource('rentals', RentalController::class);
    Route::get('/rentals/car/{carId}', [RentalController::class, 'getRentalsByCar']);
    Route::post('/rentals/return', [RentalController::class, 'returnCar']);
    Route::post('/rentals/payment', [RentalController::class, 'updatePaymentStatus']);
    
    // Statistics
    Route::get('/statistics', [RentalController::class, 'getStatistics']);
});
