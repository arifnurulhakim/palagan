<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Level;
use App\Models\User;

use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $offset = $request->query('offset');
        $limit = $request->query('limit');
    
        // Set nilai default jika offset dan limit kosong
        if (empty($offset)) {
            $offset = 0; // Atur ke offset awal jika offset kosong
        }
    
        if (empty($limit)) {
            $limit = PHP_INT_MAX; // Atur ke nilai maksimum jika limit kosong
        }
    
        try {
            $user = Auth::guard('user')->user();
            if (!$user || $user->status != 1) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Not Authorized',
                    'error_code' => 'not_authorized'
                ], 401);
            }
    
            $users = User::select('id', 'name', 'nick', 'status', 'user_date', DB::raw("DATE_FORMAT(user_date, '%e %b %Y') AS user_date_string"))
                ->where('id', '!=', 0)
                ->orderBy('user_date', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();
            $totalRecords = User::count();
    
            // Mengubah peran berdasarkan status
            foreach ($users as $user) {
                $user->role = ($user->status == 1) ? 'Admin' : 'Player';
            }
    
            return response()->json([
                'status' => 'SUCCESS',
                'draw' => 0,
                'iTotalDisplayRecords' => $totalRecords,
                'iTotalRecords' => count($users),
                'offset' => $offset,
                'limit' => $limit,
                'data_total' => $totalRecords,
                'data' => $users,
                'order_name' => '',
                'order_dir' => '',
                'search' => ''
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function userDaily(Request $request, $date = null)
    {
        try {
            // Validate the request
            $request->validate([
                'offset' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ]);
    
            $offset = $request->input('offset'); // Set default to 0 if both 'offset' and 'start' are not provided
            $limit = $request->input('limit'); // Set default to 10 if both 'limit' and 'length' are not provided
    
            // Ensure user is authorized
            $user = Auth::guard('user')->user();
            if (!$user || $user->status != 1) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Not Authorized',
                    'error_code' => 'not_authorized'
                ], 401);
            }
    
            $query = "
            SELECT pc.playercount, pc.submit_date, pc.submit_date_string, pt.playtime
            FROM (
                SELECT COUNT(DISTINCT(id_user)) AS playercount, 
                       DATE_FORMAT(submit_time, '%Y-%m-%d') AS submit_date, 
                       DATE_FORMAT(submit_time, '%e %b %Y') AS submit_date_string 
                FROM plgn_user_activity 
                WHERE activity_key = 'login' 
                GROUP BY DATE_FORMAT(submit_time, '%Y-%m-%d') 
            ) pc
            LEFT JOIN (
                SELECT AVG(activity_value) AS playtime, 
                       DATE_FORMAT(submit_time, '%Y-%m-%d') AS submit_date 
                FROM plgn_user_activity 
                WHERE activity_key = 'playtime' 
                GROUP BY DATE_FORMAT(submit_time, '%Y-%m-%d') 
            ) pt ON pt.submit_date = pc.submit_date
            ORDER BY pc.submit_date_string ASC;
            ";
            $results="";
            if (!$offset && !$limit) {
                $results = DB::select($query);
            }else{
                $results = DB::select($query)->limit($limit)->offset($offset);
            }
    
            // Menghitung jumlah total data
            $totalRecords = count($results);

            for ($i = 0; $i < count($results); $i++) {
                $results[$i]->playtime_hours = intdiv($results[$i]->playtime, 60) . 'h:' . ($results[$i]->playtime % 60) . 'm';
            }
    
            // Mendefinisikan variabel $draw, $colName, $colDir, dan $search sesuai kebutuhan Anda
            $draw = $request->input('draw', 1);
            $colName = $request->input('order_name', '');
            $colDir = $request->input('order_dir', 'asc');
            $search = $request->input('search', '');
    
            return response()->json([
                'status' => 'SUCCESS',
                'draw' => intval($draw),
                'iTotalDisplayRecords' => $totalRecords,
                'iTotalRecords' => $totalRecords,
                'offset' => $offset,
                'limit' => $limit,
                'data_total' => $totalRecords,
                'data' => $results,
                'order_name' => $colName,
                'order_dir' => $colDir,
                'search' => $search,
            ]);
        } catch (Exception $e) {
            // Handle exceptions here
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'error_code' => 'exception_error',
            ]);
        }
    }

    public function userMonthly(Request $request)
{
    try {
        // Validate the request
        $request->validate([
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
        ]);

        $offset = $request->input('offset');
        $limit = $request->input('limit');

        // Ensure user is authorized
        $user = Auth::guard('user')->user();
        if (!$user || $user->status != 1) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not Authorized',
                'error_code' => 'not_authorized'
            ], 401);
        }

        $query = "
        SELECT pc.playercount, pc.submit_date, pt.playtime, pc.submit_date_string
        FROM (
            SELECT COUNT(DISTINCT(id_user)) AS playercount, 
                   DATE_FORMAT(submit_time, '%Y-%m') AS submit_date,
                   DATE_FORMAT(submit_time, '%e %b %Y') AS submit_date_string 
            FROM plgn_user_activity 
            WHERE activity_key = 'login' 
            GROUP BY DATE_FORMAT(submit_time, '%Y-%m')
        ) pc
        LEFT JOIN (
            SELECT AVG(activity_value) AS playtime, 
                   DATE_FORMAT(submit_time, '%Y-%m') AS submit_date
            FROM plgn_user_activity 
            WHERE activity_key = 'playtime' 
            GROUP BY DATE_FORMAT(submit_time, '%Y-%m')
        ) pt ON pt.submit_date = pc.submit_date
        ORDER BY pc.submit_date ASC;
        
        ";

        $results = "";

        if (!$offset && !$limit) {
            $results = DB::select($query);
        } else {
            $results = DB::select($query)->limit($limit)->offset($offset);
        }

        // Menghitung jumlah total data
        $totalRecords = count($results);

        // Mengonversi tanggal ke format bulan dan tahun
        for ($i = 0; $i < count($results); $i++) {
            $results[$i]->playtime_hours = intdiv($results[$i]->playtime, 60) . 'h:' . ($results[$i]->playtime % 60) . 'm';
        }

        // Mendefinisikan variabel $draw, $colName, $colDir, dan $search sesuai kebutuhan Anda
        $draw = $request->input('draw', 1);
        $colName = $request->input('order_name', '');
        $colDir = $request->input('order_dir', 'asc');
        $search = $request->input('search', '');

        return response()->json([
            'status' => 'SUCCESS',
            'draw' => intval($draw),
            'iTotalDisplayRecords' => $totalRecords,
            'iTotalRecords' => $totalRecords,
            'offset' => $offset,
            'limit' => $limit,
            'data_total' => $totalRecords,
            'data' => $results,
            'order_name' => $colName,
            'order_dir' => $colDir,
            'search' => $search,
        ]);
    } catch (Exception $e) {
        // Handle exceptions here
        return response()->json([
            'status' => 'ERROR',
            'message' => $e->getMessage(),
            'error_code' => 'exception_error',
        ]);
    }
}
public function leaderboard(Request $request)
{
    $offset = $request->query('offset');
    $limit = $request->query('limit');
    try {
        $user = Auth::guard('user')->user();
        if (!$user || $user->status != 1) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Not Authorized',
                'error_code' => 'not_authorized'
            ], 401);
        }

        $users = User::select('id', 'name', 'nick', 'kills', 'deaths', 'score', 'coins', 'playtime')
            ->where('id', '!=', 0)
            ->orderBy('user_date', 'desc');

        $totalRecords = $users->count();

        if ($offset !== null || $limit !== null) {
            $users->skip($offset)->take($limit);
        }

        $results = $users->get();

        for ($i = 0; $i < count($results); $i++) {
            $split_coins = explode('&', $results[$i]->coins);
            $results[$i]->xp_coins = $split_coins[0];
            $results[$i]->gold_coins = $split_coins[1];
            $results[$i]->playtime_hours = intdiv($results[$i]->playtime, 60) . 'h:' . ($results[$i]->playtime % 60) . 'm';
        }

        return response()->json([
            'status' => 'SUCCESS',
            'draw' => 0,
            'iTotalDisplayRecords' => $totalRecords,
            'iTotalRecords' => count($results),
            'offset' => $offset,
            'limit' => $limit,
            'data_total' => $totalRecords,
            'data' => $results,
            'order_name' => '',
            'order_dir' => '',
            'search' => ''
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    
    
}

