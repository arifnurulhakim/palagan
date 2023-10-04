<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Player;

class ScoreController extends Controller
{
    // CMS
    public function index()
    {
        try {
            $scores = DB::table('scores')
            ->leftJoin('players', 'scores.player_id', '=', 'players.id')
            ->select('scores.*', 'players.username')
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $scores,
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
                'score' => 'required|integer',
                // Add more validation rules if necessary
            ]);
            $game = Game::where('code',$request->get('game_code'))->first();
            // dd($game);
            if($game == null){
                return response()->json([
                    'status' => 'error',
                    'message' => 'game code not found',
                    'error_code' => 'GAME_CODE_NOT_FOUND'
                ], 422);
            }
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }
    
            $score = Score::create([
                'player_id' => $playerId,
                'game_code' => $request->input('game_code'),
                'score' => $request->input('score'),
            ]);
    
            return response()->json([
                'status' => 'success',
                'data' => $score,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $score = DB::table('scores')
            ->leftJoin('players', 'scores.player_id', '=', 'players.id')
            ->select('scores.*', 'players.username')
            ->where('scores.id', $id)
            ->first();


            return response()->json([
                'status' => 'success',
                'data' => $score,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    
    public function showbyplayerid($id)

    {
        try {
            $scoreByGameCode = Score::select('game_code')
            ->selectRaw('SUM(score) as total_score')
            ->where('player_id', $id)
            ->groupBy('game_code')
            ->get();

            $player = DB::table('players')
            ->select('username')
            ->where('id', $id)
            ->first();


                $totalScores = [];
                foreach ($scoreByGameCode as $score) {
                $totalScores['total_score_' . $score->game_code] = $score->total_score;
                }
            $score = Score::where('player_id',$id)->get();
    
            if($score){
                return response()->json([
                    'status' => 'success',
                    'username' => $player->username,
                    'total_score' => $totalScores,
                    'data' => $score,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Score not found',
                        'error_code' => 'SCORE_NOT_FOUND'
                    ], 404);
                }
        }

        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

 
    public function showbygamecodeplayer($game_code,$playerId)

    {
        try {
            $totalScores = Score::where('player_id', $playerId)
            ->where('game_code',$game_code)
            ->sum('score');
            $score = Score::where('player_id',$playerId)
            ->where('game_code',$game_code)
            ->leftJoin('players', 'scores.player_id', '=', 'players.id')
            ->select('scores.*', 'players.username')
            ->get();
    
            if($score){
                return response()->json([
                    'status' => 'success',
                    'game_code' =>$game_code,
                    'total_score' => $totalScores,
                    'data' => $score,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Score not found',
                        'error_code' => 'SCORE_NOT_FOUND'
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
                'score' => 'required|integer',
                // Add more validation rules if necessary
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'error_code' => 'INPUT_VALIDATION_ERROR'
                ], 422);
            }

            $score = Score::findOrFail($id);
            $score->update($request->all());

            return response()->json([
                'status' => 'success',
                'data' => $score,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $score = Score::findOrFail($id);
    // dd($score);
            if ($score) {
                $score->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'score deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'score not found',
                    'error_code' => 'score_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function leaderboard($game_code)
    {
        try {
            $leaderboard = Score::leftJoin('players', 'scores.player_id', '=', 'players.id')
            ->select('scores.player_id', 'players.username')
            ->selectRaw('SUM(score) as total_score')
            ->where('game_code', $game_code)
            ->groupBy('scores.player_id','players.username')
            ->orderByDesc('total_score')
            ->get();

        // Add ranks to the leaderboard data
        $leaderboardWithRank = $leaderboard->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });
    
            return response()->json([
                'status' => 'success',
                'data' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }    
    // GAME
    public function leaderboardplayer($game_code)
    {
        try {
            $playerId = Auth::id();
    
            $rank = Score::select('player_id')
                ->selectRaw('SUM(score) as total_score')
                ->where('game_code', $game_code)
                ->where('player_id', $playerId)
                ->groupBy('player_id')
                ->orderByDesc('total_score')
                ->get();
    
            $leaderboard = Score::select('player_id')
                ->selectRaw('SUM(score) as total_score')
                ->where('game_code', $game_code)
                ->groupBy('player_id')
                ->orderByDesc('total_score')
                ->get();
    
            // Add ranks to the leaderboard data
            $leaderboardWithRank = $leaderboard->map(function ($item, $index) use ($rank) {
                $item['rank'] = $index + 1;
                return $item;
            });
    
            $selfRank = $rank->map(function ($item) use ($leaderboardWithRank) {
                $item['rank'] = $leaderboardWithRank->where('player_id', $item['player_id'])->first()['rank'];
                return $item;
            });
    
            return response()->json([
                'status' => 'success',
                'peringkat' => $selfRank,
                'data' => $leaderboardWithRank,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
      
    
    public function showbygamecode($game_code)

    {
        try {
            $playerId = Auth::id();
            $totalScores = Score::where('player_id', $playerId)
            ->where('game_code',$game_code)
            ->sum('score');
            $score = Score::where('player_id',$playerId)
            ->where('game_code',$game_code)
            ->get();
    
            if($score){
                return response()->json([
                    'status' => 'success',
                    'game_code' =>$game_code,
                    'total_score' => $totalScores,
                    'data' => $score,
                ]);
            }
            
                else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Score not found',
                        'error_code' => 'SCORE_NOT_FOUND'
                    ], 404);
                }
        }

        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function showbyplayer()
    {
        try {
            $playerId = Auth::id();
            $scoreByGameCode = Score::select('game_code')
            ->selectRaw('SUM(score) as total_score')
            ->where('player_id', $playerId)
            ->groupBy('game_code')
            ->get();

                $totalScores = [];
                foreach ($scoreByGameCode as $score) {
                $totalScores['total_score_' . $score->game_code] = $score->total_score;
                }
            $score = Score::where('player_id', $playerId)->get();
    
            if ($score->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'total_score' => $totalScores,
                    'data' => $score,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Score not found',
                    'error_code' => 'SCORE_NOT_FOUND'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
