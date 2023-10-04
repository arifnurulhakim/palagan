<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LevelController extends Controller
{
    public function index()
    {
        try {
            $levels = DB::table('levels')
                ->leftJoin('players', 'levels.player_id', '=', 'players.id')
                ->select('levels.*', 'players.username')
                ->get();
    
            return response()->json([
                'status' => 'success',
                'data' => $levels,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
{
    try {
        // Mengambil player_id dari pengguna yang terotentikasi
        $playerId = Auth::id();

        $validator = Validator::make($request->all(), [
            'game_code' => 'required',
            'level' => 'required',
           
            // Tambahkan aturan validasi lain jika diperlukan
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
                'error_code' => 'INPUT_VALIDATION_ERROR'
            ], 422);
        }

        $level = Level::create([
            'player_id' => $playerId,
            'game_code' => $request->game_code,
            'level' => $request->level,
            'log_time' => $request->log_time,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $level,
        ], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
    public function storebyadmin(Request $request)
{
    try {
        // Mengambil player_id dari pengguna yang terotentikasi
        

        $validator = Validator::make($request->all(), [
            'player_id' => 'required',
            'game_code' => 'required',
            'level' => 'required',
           
            // Tambahkan aturan validasi lain jika diperlukan
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
                'error_code' => 'INPUT_VALIDATION_ERROR'
            ], 422);
        }

        $level = Level::create([
            'player_id' => $request->player_id,
            'game_code' => $request->game_code,
            'level' => $request->level,
            'log_time' => $request->log_time,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $level,
        ], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function show($id)
{
    try {
        $level = DB::table('levels')
            ->leftJoin('players', 'levels.player_id', '=', 'players.id')
            ->select('levels.*', 'players.username')
            ->where('levels.id', $id)
            ->first();

        if ($level) {
            return response()->json([
                'status' => 'success',
                'data' => $level,
            ]);
        } else {
            return response()->json(['error' => 'Player not found'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function showbyplayer()
    {
        try {
            $playerId = Auth::id();
    
            $levels = Level::where('player_id', $playerId)->get();
    
            if ($levels->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Level not found',
                    'error_code' => 'LEVEL_NOT_FOUND'
                ], 404);
            }
    
            return response()->json([
                'status' => 'success',
                'data' => $levels,
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function showbyplayerid($id)

    {
        try {
            $level = Level::where('levels.player_id',$id)
            ->leftJoin('players', 'levels.player_id', '=', 'players.id')
            ->select('levels.*', 'players.username')
            ->get();
    
            if($level){
                return response()->json([
                    'status' => 'success',
                    'data' => $level,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Level not found',
                        'error_code' => 'LEVEL_NOT_FOUND'
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
                'level' => 'required',
               
                // Tambahkan aturan validasi lain jika diperlukan
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $level = Level::findOrFail($id);
            $level->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $level,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatebyadmin(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'player_id' => 'required',
                'game_code' => 'required',
                'level' => 'required',
               
                // Tambahkan aturan validasi lain jika diperlukan
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $level = Level::findOrFail($id);
            $level->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $level,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $level = Level::findOrFail($id);
    // dd($level);
            if ($level) {
                $level->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Level deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Level not found',
                    'error_code' => 'LEVEL_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}
