<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function index()
    {
        try {
            $currencies = Currency::all();

            return response()->json([
                'status' => 'success',
                'data' => $currencies,
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
                'code' => 'required|string|max:255|unique:currencies',
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $currency = Currency::create($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $currency,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Currency $currency)
    {
        try {
            if($currency){
                return response()->json([
                    'status' => 'success',
                    'data' => $currency,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Currency not found',
                        'error_code' => 'CURRENCY_NOT_FOUND'
                    ], 404);
                }
        }

        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Currency $currency)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:currencies,code,' . $currency->id,
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $currency->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $currency,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Currency $currency)
    {
        try {

           
            if($currency){
                $currency->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Currency deleted successfully',
                ], 204);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Currency not found',
                        'error_code' => 'CURRENCY_NOT_FOUND'
                    ], 404);
                }
        }
    
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
