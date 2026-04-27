<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;

Route::get('/rooms', [RoomController::class, 'index']);

Route::post('/rooms', [RoomController::class, 'store']);

Route::put('/rooms/{id}', [RoomController::class, 'update']);

Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

Route::get('/reservations', [ReservationController::class, 'index']);

Route::get('/reservations/{id}', [ReservationController::class, 'show']);

Route::post('/reservations', [ReservationController::class, 'store']);

Route::put('/reservations/{id}', [ReservationController::class, 'update']);

Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

Route::post('/suggestions', [ReservationController::class, 'suggestions']);
