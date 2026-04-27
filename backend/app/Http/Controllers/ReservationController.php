<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        \Log::info($validated);

        //REGRA: não pode conflito de horário
        $conflict = Reservation::where('room_id', $validated['room_id'])
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                    ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();
        
        if ($conflict) {

    $availableRooms = \App\Models\Room::whereDoesntHave('reservations', function ($query) use ($validated) {
        $query->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time']);
    })->get();

    return response()->json([
        'message' => 'Sala já está reservada nesse horário',
        'available_rooms' => $availableRooms
    ], 409);
}

        $reservation = Reservation::create($validated);

        return response()->json($reservation, 201);
    }

    public function index()
    {
        $reservations = \App\Models\Reservation::with(['user', 'room'])->get();

        return response()->json($reservations);
    }

    public function show($id)
    {
        $reservation = \App\Models\Reservation::with(['user', 'room'])->find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        return response()->json($reservation);
    }

    public function update(Request $request, $id)
    {
        $reservation = \App\Models\Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // REGRA DE CONFLITO (ignorando a própria reserva)
        $conflict = \App\Models\Reservation::where('room_id', $validated['room_id'])
            ->where('id', '!=', $id)
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                    ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Sala já está reservada nesse horário'
            ], 409);
        }

        $reservation->update($validated);

        return response()->json($reservation);
    }

    public function destroy($id)
    {
        $reservation = \App\Models\Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        $reservation->delete();

        return response()->json([
            'message' => 'Reserva deletada com sucesso'
        ]);
    }

    public function suggestions(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        // 1. buscar salas sem conflito nesse horário
        $availableRooms = \App\Models\Room::whereDoesntHave('reservations', function ($query) use ($validated) {
            $query->where('start_time', '<', $validated['end_time'])
                ->where('end_time', '>', $validated['start_time']);
        })->get();

        return response()->json([
            'message' => 'Sugestões encontradas',
            'available_rooms' => $availableRooms
        ]);
    }
}
