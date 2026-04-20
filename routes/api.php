<?php

use App\Http\Controllers\RoomController;

Route::get('/rooms', [RoomController::class, 'index']);