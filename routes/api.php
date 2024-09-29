<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WindowsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Api\V1\BookingController;

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::post('register', [ApiController::class, 'register']);
        Route::post('login', [ApiController::class, 'login']);
        Route::get('allForms', [UserController::class, 'allForms']);

        Route::group([
            'middleware' => ['auth:sanctum'],
        ], function () {
            Route::get('all', [UserController::class, 'all']);
            Route::post('book', [BookingController::class, 'store']);
            Route::get('getBookings', [BookingController::class, 'bookedTimes']);
            Route::get('getLastAppointmentNum', [BookingController::class, 'getLastAppointmentNum']);
            Route::get('getUserBookings', [UserController::class, 'getUserBookings']);
        })->middleware('auth:sanctum');
    });

    Route::prefix('admin')->group(function () {
        Route::group([
            'middleware' => ['auth:sanctum'],
        ], function () {
            Route::get('allWindow', [WindowsController::class, 'all']);
            Route::post('createWindow', [WindowsController::class, 'create']);
        })->middleware('auth:sanctum');
    });

    Route::prefix('teller')->group(function () {
        Route::group([
            'middleware' => ['auth:sanctum'],
        ], function () {
            Route::get('allBookings', [BookingController::class, 'getAllBooking']);
            Route::get('queuedOnline', [BookingController::class, 'updateQueueOnline']);
            Route::get('getCurrentRequestServing', [BookingController::class, 'getCurrentRequestServing']);
            Route::post('serveCurrent', [BookingController::class, 'serveCurrent']);
            Route::post('updateBookingStatus', [BookingController::class, 'updateBookingStatus']);
        })->middleware('auth:sanctum');
    });
});