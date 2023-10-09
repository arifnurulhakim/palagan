<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache cleared</h1>';
})->name('clear-cache');

Route::get('/route-clear', function () {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
})->name('route-clear');

Route::get('/config-cache', function () {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Configuration cached</h1>';
})->name('config-cache');

Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize:clear');
    return '<h1>Configuration cached</h1>';
})->name('optimize');

Route::get('/storage-link', function () {
    $exitCode = Artisan::call('storage:link');
    return '<h1>storage linked</h1>';
})->name('storage-link');
Route::get('/unauthorized', function () {
    abort(401, 'Unauthorized');
})->name('Unauthorized');

Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);
Route::post('/admin/passwordUpdate', [AuthController::class, 'resetPassword']);

Route::get('/admin/passwordReset', [ForgotPasswordController::class, '__invoke']);
Route::post('/admin/passwordReset', [ForgotPasswordController::class, '__invoke']);

Route::get('/user',  [PlayerController::class, 'index']);
Route::get('/userDaily',  [PlayerController::class, 'userDaily']);
Route::get('/userMonthly',  [PlayerController::class, 'userMonthly']);
Route::get('/leaderboard',  [PlayerController::class, 'leaderboard']);



Route::get('/password/code/check', [CodeCheckController::class, '__invoke'])->name('check');
Route::post('/password/code/check', [CodeCheckController::class, '__invoke'])->name('check');



Route::get('/password/reset', [ResetPasswordController::class, '__invoke'])->name('postreset');
Route::post('/password/reset', [ResetPasswordController::class, '__invoke'])->name('reset');


Route::post('/reset-first-password', [ResetPasswordController::class, 'resetFirstPassword'])->name('reset-first-password');









Route::post('/playersLogin', [PlayerController::class, 'login'])->middleware('player');

Route::apiResource('players', PlayerController::class);
Route::get('/getprofile',  [PlayerController::class, 'getprofile'])->name('getprofile');
Route::get('/getprofileadmin',  [PlayerController::class, 'getprofileadmin'])->name('getprofileadmin');
Route::get('/logout',  [PlayerController::class, 'logout'])->name('logout');
Route::get('/logoutAdmin',  [PlayerController::class, 'logoutAdmin'])->name('logoutAdmin');


  

// Route::middleware('checktokenuser')->group(function () {
//     Route::apiResource('currency', CurrencyController::class);
//     Route::apiResource('config', ConfigController::class);
//     Route::get( '/getplayerbygame/{game_code}',[PlayerController::class, 'getplayerbygame']);
//     Route::apiResource('games', GameController::class);

//     Route::apiResource('levels', LevelController::class);
//     Route::get('levelsPlayerId/{id}', [LevelController::class, 'showbyplayerid']);
//     Route::post('storebyadmin', [LevelController::class, 'storebyadmin']);
    

//     Route::get('/events', [EventController::class, 'index']);
//     Route::get('/events/{event}', [EventController::class, 'show']);
//     Route::put('/events/{event}', [EventController::class, 'update']);
//     Route::delete('/events/{event}', [EventController::class, 'destroy']);
//     Route::get('eventsPlayerId/{id}', [EventController::class, 'showbyplayerid']);
    
   
//     Route::get('/scores', [ScoreController::class, 'index']);
//     Route::get('/scores/{score}', [ScoreController::class, 'show']);
//     Route::put('/scores/{score}', [ScoreController::class, 'update']);
//     Route::delete('/scores/{score}', [ScoreController::class, 'destroy']);
//     Route::get('scoresPlayerId/{id}', [ScoreController::class, 'showbyplayerid']);
//     Route::get('leaderboard/{game_code}', [ScoreController::class, 'leaderboard']);
//     Route::get('scoresPlayerId/{game_code}/{player_id}', [ScoreController::class, 'showbygamecodeplayer']);


//     Route::get('/wallets', [WalletController::class, 'index']);
//     Route::get('/wallets/{wallet}', [WalletController::class, 'show']);
//     Route::put('/wallets/{wallet}', [WalletController::class, 'update']);
//     Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy']);
//     Route::get('walletsPlayerId/{id}', [WalletController::class, 'showbyplayerid']);
// });
// Route::middleware('checktokenplayer')->group(function () {
   
//     Route::post('/events', [EventController::class, 'store']); 
//     Route::post('/levels', [LevelController::class,'store']);
//     Route::get('levelsPlayer', [LevelController::class, 'showbyplayer']);
//     Route::post('/scores', [ScoreController::class, 'store']);
//     Route::post('/wallets', [WalletController::class, 'store']);
//     Route::get('scoresPlayer', [ScoreController::class, 'showbyplayer']);
//     Route::get('leaderboardplayer/{game_code}', [ScoreController::class, 'leaderboardplayer']);
//     Route::get('scoresPlayer/{game_code}', [ScoreController::class, 'showbygamecode']);
//     Route::get('wallestPlayer', [WalletController::class, 'showbyplayer']);
//     Route::post('convertDiamond', [WalletController::class, 'convertDiamond']);
//     Route::post('convertCoin', [WalletController::class, 'convertCoin']);
  

// });
