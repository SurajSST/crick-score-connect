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



Route::post('/login',[AuthController::class,'login'])->name('api.login');
Route::post('/register',[AuthController::class,'register'])->name('api.register');
Route::post('/forgotPassword',[AuthController::class,'forgotPassword'])->name('api.forgotPassword');
Route::post('/logout',[AuthController::class,'logout'])->name('api.logout');
Route::get('/users/search', [ApiController::class, 'searchUsers'])->name('api.users.search');


Route::apiResource('teams', TeamController::class);
Route::apiResource('team-players', TeamController::class);
Route::apiResource('matches', MatchesController::class);
Route::apiResource('innings', InningsController::class);
Route::apiResource('battingstats', BattingStatsController::class);
Route::apiResource('bowlingstats', BowlingStatsController::class);


Route::middleware(['auth:sanctum', config('jetstream.auth_session'),'verified',])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
