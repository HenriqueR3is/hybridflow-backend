<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Room;

class RoomController extends Controller
{
    public function index()
    {
        return Room::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ]);

        $room = Room::create($validated);

        return response()->json($room, 201);
    }

        public function destroy($id)
    {
        $room = \App\Models\Room::find($id);

        if (!$room) {
            return response()->json([
                'message' => 'Sala não encontrada'
            ], 404);
        }

        $room->delete();

        return response()->json([
            'message' => 'Sala deletada com sucesso'
        ]);
    }

    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'message' => 'Sala não encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $room->update($validated);

        return response()->json($room);
    }
}
