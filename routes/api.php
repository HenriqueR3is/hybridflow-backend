<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;

Route::get('/rooms', [RoomController::class, 'index']);

Route::post('/rooms', [RoomController::class, 'store']);

Route::post('/reservations', [ReservationController::class, 'store']);

Route::get('/reservations', [ReservationController::class, 'index']);

Route::get('/reservations/{id}', [ReservationController::class, 'show']);

Route::put('/reservations/{id}', [ReservationController::class, 'update']);

Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
