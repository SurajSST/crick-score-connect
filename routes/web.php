<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Artisan;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::redirect('/', '/admin');

Route::redirect('/admin', '/admin/dashboard');
Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return redirect()->back();
})->name('storage.link');

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
        Route::get('/user/show', [AdminController::class, 'allUsers'])->name('user.show');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('user.delete');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('user.update');
    });
});
