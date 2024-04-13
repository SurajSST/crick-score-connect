<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\InningsController;
use App\Http\Controllers\BattingStatsController;
use App\Http\Controllers\BowlingStatsController;


Route::post('/login', [AuthController::class, 'login'])->name('api.login')->middleware('checkPostMethod');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword'])->name('api.forgotPassword');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
Route::put('/users/{id}', [ApiController::class, 'userEdit'])->name('api.user.edit');
Route::post('/users/search', [ApiController::class, 'searchUsers'])->name('api.users.search');

Route::get('/user/{user_id}/stats', [ApiController::class, 'getUserStats'])->name('api.getUserStats');

Route::post('friend-requests', [ApiController::class, 'sendFriendRequest'])->name('api.friend-requests.send');
Route::put('friend-requests/{requestId}/confirm', [ApiController::class, 'confirmFriendRequest'])->name('api.friend-requests.confirm');
Route::put('friend-requests/{requestId}/reject', [ApiController::class, 'rejectFriendRequest'])->name('api.friend-requests.reject');
Route::get('users/{userId}/friend-requests', [ApiController::class, 'searchFriendRequests'])->name('api.friend-requests.search');
Route::get('users/{userId}/friends', [ApiController::class, 'searchFriendList'])->name('api.friends.search');

Route::apiResource('teams', TeamController::class);
Route::apiResource('team-players', TeamController::class);
Route::apiResource('matches', MatchesController::class);
Route::apiResource('innings', InningsController::class);
Route::apiResource('battingstats', BattingStatsController::class);
Route::apiResource('bowlingstats', BowlingStatsController::class);


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
