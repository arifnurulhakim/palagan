<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;




class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
            $credentials = $request->only('name', 'password');
            Auth::shouldUse('user');
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'username or password invalid',
                    'error_code' => 'USERNAME_OR_PASSWORD_INVALID'
                ], 401);
            }
    
            $user = Auth::user();
            if($user->status != 1){
                return response()->json([
                    'status'=>'ERROR', 
                    'message'=>'Admin Not Found or User not Admin', 
                    'error_code'=>'admin_not_found'
                ], 404);
            }
    
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'status' => 'success',
                'id' => $user->id,
                'name' => $user->name,
                'nick' => $user->nick,
                'role' => 'admin',
                'token'=> $token
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request)
{
    try {
        $user = Auth::guard('user')->user();
        if (!$user) {
            return response()->json([
                'status'=>'ERROR', 
                'message'=>'Not Authorized', 
                'error_code'=>'not_authorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'password_current' => 'required|string|min:6',
            'password_new' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
                'error_code' => 'INPUT_VALIDATION_ERROR'
            ], 422);
        }

        // Periksa apakah kata sandi saat ini cocok dengan kata sandi yang ada di database
        if (!Hash::check($request->password_current, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
                'error_code' => 'INVALID_PASSWORD'
            ], 422);
        }

        // Mengganti kata sandi dengan kata sandi baru yang di-hash
        $user->password = bcrypt($request->password_new);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function register(Request $request)
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

    public function getUser($id)
    {
        try {
            $user = User::where('id',$id)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'user not found',
                    'error_code' => 'USER_NOT_FOUND'
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

    public function getAllUser()
    {
        try {
            $users = User::orderBy('name', 'asc')->get();
            $userArray = [];
            foreach ($users as $user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
                array_push($userArray, $userData);
            }
            return response()->json([
                'status' => 'success',
                'data' => $userArray,
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:users,email,'.$id,
                'password' => 'string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $user->name = $request->name ?? $user->name;
            $user->email = $request->email ?? $user->email;
            $user->password = $request->password ? bcrypt($request->password) : $user->password;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $user = User::find($request->id);
            if ($user) {
                $user->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'User with name ' . $user->name .' and with email '.$user->email . ' has been deleted.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User with name ' . $user->name .' and with email '.$user->email . ' not found.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportCSV()
    {
        try {
            $data = User::select('id', 'name', 'email')->orderBy('name', 'asc')->get(); // query data dari database
            $dateStart = date('Ymd');
            $filename = "users_".$dateStart.".csv";
            //local
            $filename_path = public_path('storage/csv/' . $filename); // path to save CSV file in public/storage/csv
            
            //server
            // $filename_path = '/home/doddiplexus/doddi.plexustechdev.com/templete/api/public/csv/' . $filename;
   
            // buat file CSV
            $handle = fopen($filename_path, 'w');
            fputcsv($handle, ['ID', 'Name', 'Email']);
            foreach($data as $row) {
                fputcsv($handle, [$row->id, $row->name, $row->email]);
            }
            fclose($handle);
    
            // Kasih balikan nama file & urlnya
            $filename_url = url('storage/csv/'.$filename);
            return response()->json([
                'status' => 'SUCCESS',
                'filename' => $filename,
                'filename_url' => $filename_url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Failed to export CSV: ' . $e->getMessage()
            ], 500);
        }
    }
    




}

