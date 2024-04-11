<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});
Route::redirect('/admin', '/admin/dashboard');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('index');
        Route::get('/users', [AdminController::class, 'allUsers'])->name('users');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('user.delete');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('user.update');

    });
});
