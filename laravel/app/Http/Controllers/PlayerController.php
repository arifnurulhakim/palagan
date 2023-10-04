<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;




class PlayerController extends Controller
{
   
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
    
            $credentials = $request->only('username', 'password');
            Auth::shouldUse('player');
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Username or password invalid',
                    'error_code' => 'USERNAME_OR_PASSWORD_INVALID'
                ], 401);
            }
    
            $player = Auth::user();
            $token = JWTAuth::fromUser($player);
    
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $player->id,
                    'player' => $player->name,
                    'username' => $player->username,
                    'token' => $token,
                ],
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function adminLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
    
            $credentials = $request->only('email', 'password');
            Auth::shouldUse('user');
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'email or password invalid',
                    'error_code' => 'EMAIL_OR_PASSWORD_INVALID'
                ], 401);
            }
    
            $user = Auth::user();
    
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'user' => $user->name,
                    'email' => $user->email,
                    'token' => $token,
                ],
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
     

    public function AdminRegister(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => bcrypt($request->get('password')),
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json(compact('user','token'),201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized, please login again',
                    'error_code' => 'USER_NOT_FOUND'
                ], 401);
            }
            if (!Auth::check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token',
                    'error_code' => 'INVALID_TOKEN'
                ], 401);
            }
    
            Auth::logout();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function logoutAdmin(Request $request)
{
    try {
        $user = Auth::guard('user')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, please login again',
                'error_code' => 'USER_NOT_FOUND'
            ], 401);
        }

        Auth::guard('user')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function getProfile()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'error_code' => 'UNAUTHORIZED'
                ], 401);
            }
    
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
    
            return response()->json([
                'status' => 'success',
                'data' => $userData,
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getprofileadmin()
    {
        try {
            $user = Auth::guard('user')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'error_code' => 'UNAUTHORIZED'
                ], 401);
            }
    
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
    
            return response()->json([
                'status' => 'success',
                'data' => $userData,
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        try {
            $players = Player::all()->makeHidden(['password', 'role']);
            return response()->json([
                'status' => 'success',
                'data' => $players,
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
                'email' => 'required|string|email|max:255',
                'picture' => 'nullable|string',
                'username' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
            $getplayer=Player::where('username', $request->get('username'))
            
            ->first();
            if($getplayer){
                return response()->json([
                    'status' => 'error',
                    'message' => 'player already use',
                    'error_code' => 'PLAYER_ALREADY_USE'
                ], 422);
            }
    
            $player = Player::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'picture' => $request->get('picture'),
                'username' => $request->get('username'),
                'password' => bcrypt($request->get('password')),
            ]);
    
            $player->makeHidden(['password']);
    
            return response()->json([
                'status' => 'success',
                'data' => $player,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $player = Player::findOrFail($id);
    
            return response()->json([
                'status' => 'success',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getplayerbygame($game_code)
    {
        try {
            $player = Level::select('levels.game_code', 'levels.player_id', 'players.name','players.username','players.email')
            ->join('players', 'levels.player_id', '=', 'players.id')
            ->where('levels.game_code', $game_code)
            ->get();
        
    
            return response()->json([
                'status' => 'success',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $player = Player::findOrFail($id);
    
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'picture' => 'nullable|string',
                'username' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
    
            $player->update([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'picture' => $request->get('picture'),
                'username' => $request->get('username'),
                'password' => bcrypt($request->get('password')),
            ]);
    
            $player->makeHidden(['password']);
    
            return response()->json([
                'status' => 'success',
                'data' => $player,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $player = Player::findOrFail($id);
    
            if ($player) {
                $player->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Player with name ' . $player->name .' and with email '.$player->email . ' has been deleted.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Player with name ' . $player->name .' and with email '.$player->email . ' not found.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function Unauthorized() {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
            'error_code' => 'UNAUTHORIZED'
        ], 401);
    }
}
