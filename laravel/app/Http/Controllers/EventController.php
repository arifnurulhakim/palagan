<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        try {
            $events = Event::all();

            return response()->json([
                'status' => 'success',
                'data' => $events,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

public function store(Request $request)
{
    try {
        $playerId = Auth::id();
     
        $validator = Validator::make($request->all(), [
            'game_code' => 'required',
            'name' => 'required|string|max:255',
            // Tambahkan aturan validasi lain jika diperlukan
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
                'error_code' => 'INPUT_VALIDATION_ERROR'
            ], 422);
        }

        $event = Event::create([
            'player_id' => $playerId,
            'game_code' => $request->game_code,
            'name' => $request->name,
            'value' => $request->value,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $event,
        ], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function show($id)
    {
        try {
            $event = Event::findOrFail($id);

            if($event){
                return response()->json([
                    'status' => 'success',
                    'data' => $event,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Event not found',
                        'error_code' => 'EVENT_NOT_FOUND'
                    ], 404);
                }
        }

        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function showbyplayerid($id)

    {
        try {
            $event = Event::where('player_id',$id)->get();
    
            if($event){
                return response()->json([
                    'status' => 'success',
                    'data' => $event,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Event not found',
                        'error_code' => 'EVENT_NOT_FOUND'
                    ], 404);
                }
        }

        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
             
                'game_code' => 'required',
                'name' => 'required|string|max:255',
                // Tambahkan aturan validasi lain jika diperlukan
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $event = Event::findOrFail($id);
            $event->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();

            if($event){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Event deleted successfully',
                ], 204);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Event not found',
                        'error_code' => 'Event_NOT_FOUND'
                    ], 404);
                }
        }
    
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
