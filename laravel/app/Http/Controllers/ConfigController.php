<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    public function index()
    {
        try {
            $config = Config::all();

            return response()->json([
                'status' => 'success',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'value' => 'required|string|max:255',
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $Config = Config::create($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $Config,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Config $Config)
    {
        try {
            if($Config){
                return response()->json([
                    'status' => 'success',
                    'data' => $Config,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Config not found',
                        'error_code' => 'CONFIG_NOT_FOUND'
                    ], 404);
                }
        }

        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Config $Config)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'description' => 'string|max:255',
                'value' => 'string|max:255',
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $Config->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $Config,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Config $Config)
    {
        try {
            $Config->delete();

            $Config->delete();
            if($Config){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Config deleted successfully',
                ], 204);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Config not found',
                        'error_code' => 'Config_NOT_FOUND'
                    ], 404);
                }
        }
    
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
