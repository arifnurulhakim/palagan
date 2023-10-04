<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Player;

class GameController extends Controller
{
    public function index()
    {
        try {
            $games = Game::all();
            return response()->json([
                'status' => 'success',
                'data' => $games,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:games',
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $game = Game::create($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $game,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $game = Game::findOrFail($id);
            if($game){
                return response()->json([
                    'status' => 'success',
                    'data' => $game,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Game not found',
                        'error_code' => 'GAME_NOT_FOUND'
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
            $game = Game::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:games,code,' . $id,
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $game->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $game,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $game = Game::findOrFail($id);
    // dd($game);
            if ($game) {
                $game->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'game deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'game not found',
                    'error_code' => 'game_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
}
