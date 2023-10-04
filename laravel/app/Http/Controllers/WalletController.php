<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Game;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Player;

class WalletController extends Controller
{
    public function index()
    {
        try {
            $wallets = DB::table('wallets')
            ->leftJoin('players', 'wallets.player_id', '=', 'players.id')
            ->select('wallets.*', 'players.username')
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $wallets,
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
                'game_code' => 'required|string|max:255',
                'currency_code' => 'required|string|max:255',
                'amount' => 'required|numeric',
                // Add more validation rules if necessary
            ]);
    
            $game = Game::where('code',$request->get('game_code'))->first();
            $currency = Currency::where('code',$request->get('currency_code'))->first();

            if($game == null){
                return response()->json([
                    'status' => 'error',
                    'message' => 'game code not found',
                    'error_code' => 'GAME_CODE_NOT_FOUND'
                ], 422);
            }
            if($currency == null){
                return response()->json([
                    'status' => 'error',
                    'message' => 'currency not found',
                    'error_code' => 'CURRENCY_NOT_FOUND'
                ], 422);
            }
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
    
            $wallet = Wallet::create([
                'player_id' => $playerId,
                'game_code' => $request->input('game_code'),
                'currency_code' => $request->input('currency_code'),
                'amount' => $request->input('amount'),
            ]);
    
            return response()->json([
                'status' => 'success',
                'data' => $wallet,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function show($id)
    {
        try {
            $wallet = DB::table('wallets')
            ->leftJoin('players', 'wallets.player_id', '=', 'players.id')
            ->select('wallets.*', 'players.username')
            ->where('wallets.id', $id)
            ->first();

            return response()->json([
                'status' => 'success',
                'data' => $wallet,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wallet not found',
                'error_code' => 'WALLET_NOT_FOUND'
            ], 404);
        }
    }

   
    
    public function showbyplayerid($id)

    {
        try {
            $wallet = Wallet::where('wallets.player_id',$id)
            ->leftJoin('players', 'wallets.player_id', '=', 'players.id')
            ->select('wallets.*', 'players.username')
            ->get();
    
    
            if($wallet){
                return response()->json([
                    'status' => 'success',
                    'data' => $wallet,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Wallet not found',
                        'error_code' => 'WALLET_NOT_FOUND'
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
                'game_code' => 'required|string|max:255',
                'currency_id' => 'required|string|max:255',
                'amount' => 'required|numeric',
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $wallet = Wallet::findOrFail($id);
            $wallet->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $wallet,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function destroy($id)
    {
        try {
            $wallet = Wallet::findOrFail($id);
    // dd($wallet);
            if ($wallet) {
                $wallet->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'wallet deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'wallet not found',
                    'error_code' => 'wallet_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    

    // GAME
    public function showbyplayer()
    {
        try {
            $playerId = Auth::id();
            
            $total_wallet = Wallet::where('player_id', $playerId)
            ->where('amount', '>', 0)
            ->sum('amount');
        
            $wallet = Wallet::where('player_id', $playerId)->get();
    
            if ($wallet->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'total_wallet' => $total_wallet,
                    'data' => $wallet,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wallet not found',
                    'error_code' => 'WALLET_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function convertDiamond(Request $request){
        try {
            $playerId = Auth::id();
            $wallet = Wallet::where('player_id', $playerId)
            ->where('currency_code', 'CNN')
            ->select('game_code')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupby('game_code')
            ->first();

             
                $validator = Validator::make($request->all(), [
                    'amount' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $validator->errors(),
                        'error_code' => 'INPUT_VALIDATION_ERROR'
                    ], 422);
                }
    
            if ($wallet->total_amount>0) {
                $conversionRate = 250; // 1 diamond = 250 coin
                $amountreqdiamond = $request->amount;
                $convertedDiamonds = floor($wallet->total_amount / $conversionRate);
                
                if($amountreqdiamond>$convertedDiamonds){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Coin kurang',
                        'error_code' => 'COIN_KURANG'
                    ], 422);
                }
                else if($amountreqdiamond<$convertedDiamonds){
                    $totalhargadiamond = ($amountreqdiamond * $conversionRate) * -1;
                    // dd($totalhargadiamond);
                    // dd($totalmincoin);
                    $newWalletEntryDmd = new Wallet();
                    $newWalletEntryDmd->player_id = $playerId;
                    $newWalletEntryDmd->game_code = $wallet->game_code;
                    $newWalletEntryDmd->currency_code = 'DMD';
                    $newWalletEntryDmd->amount = $amountreqdiamond;
                    $newWalletEntryDmd->label = "hasil conversi dari Coin jadi Diamond";
                    $newWalletEntryDmd->save();

                    $newWalletMinCoin = new Wallet();
                    $newWalletMinCoin->player_id = $playerId;
                    $newWalletMinCoin->game_code = $wallet->game_code;
                    $newWalletMinCoin->currency_code = 'CNN';
                    $newWalletMinCoin->amount = $totalhargadiamond;
                    $newWalletMinCoin->label = "Menconversikan Coin jadi Diamond";
                    $newWalletMinCoin->save();



                    // Lakukan query kembali untuk mendapatkan total wallet
                    $total_wallet = Wallet::where('player_id', $playerId)
                    ->where('currency_code', 'CNN')
                    ->selectRaw('SUM(amount) as total_amount')
                    ->first();

                            $total_dmd = Wallet::where('player_id', $playerId)
                            ->where('currency_code', 'DMD')
                            ->selectRaw('SUM(amount) as total_amount')
                            ->first();
    
                    if ($total_wallet) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Conversion to diamond successful',
                            'total_coin' => $total_wallet, // Ubah sesuai dengan kolom yang sesuai
                            'total_diamond' => $total_dmd, // Ubah sesuai dengan kolom yang sesuai
                           
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Wallet not found',
                            'error_code' => 'WALLET_NOT_FOUND'
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Insufficient coins for conversion',
                        'error_code' => 'INSUFFICIENT_COINS'
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid wallet entry found',
                    'error_code' => 'WALLET_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
    
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function convertCoin(){
        try {
            $playerId = Auth::id();
            $wallet = Wallet::where('player_id', $playerId)
                ->where('currency_code', 'DMD')
                ->where('amount', '>', 0)
                ->first();
    
            if ($wallet) {
                $conversionRate = 250; // 1 diamond = 250 coin
                $convertedCoins = floor($wallet->amount * $conversionRate);
    
                if ($convertedCoins > 0) {
                    // Begin Transaction
                    // DB::beginTransaction();
    
                    // Ubah nilai amount pada wallet menjadi negatif
                    $wallet->amount = -$wallet->amount;
                    $wallet->save();
    
                    $wallet_cnn = Wallet::where('player_id', $playerId)
                    ->where('currency_code', 'CNN')
                    ->where('amount', '>', 0)
                    ->first();

                    if($wallet_cnn){
                        $wallet_mincnn = Wallet::where('player_id', $playerId)
                        ->where('currency_code', 'DMD')
                        ->where('amount', '<', 0)
                        ->first();
                        if($wallet_mincnn){
                            $wallet_mincnn->amount = -$wallet_mincnn->amount;
                            $wallet_mincnn->save();  
                        }
                    }else{
                               // Tambahkan entri baru ke tabel wallet
                    $newWalletEntry = new Wallet();
                    $newWalletEntry->player_id = $playerId;
                    $newWalletEntry->game_code = $wallet->game_code;
                    $newWalletEntry->currency_code = 'CNN';
                    $newWalletEntry->amount = $convertedCoins;
                    $newWalletEntry->label = "Menconversikan Diamond jadi Coin";
                    $newWalletEntry->save();
                    }
                    // Lakukan query kembali untuk mendapatkan total wallet
                    $total_wallet = Wallet::where('player_id', $playerId)
                    ->where('currency_code', 'DMD')
                    ->where('amount', '>', 0)
                    ->sum('amount');
                            $total_cnn = Wallet::where('player_id', $playerId)
                            ->where('currency_code', 'CNN')
                    ->where('amount', '>', 0)
                    ->sum('amount');
    
                    if ($total_wallet) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Conversion to coin successful',
                            'total_coin' => $total_cnn, // Ubah sesuai dengan kolom yang sesuai
                            'total_diamond' => $total_wallet, // Ubah sesuai dengan kolom yang sesuai
                           
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Wallet not found',
                            'error_code' => 'WALLET_NOT_FOUND'
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Insufficient diamonds for conversion',
                        'error_code' => 'INSUFFICIENT_COINS'
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid wallet entry found',
                    'error_code' => 'WALLET_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
    
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
}
