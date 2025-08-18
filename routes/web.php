<?php

use App\Http\Controllers\CustomDeclarationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;




Route::get('/', function () {
    return view('login');
});

Route::get('/dashboard', [CustomDeclarationController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/declaration/store', [CustomDeclarationController::class, 'store'])->name('declaration.store');
Route::put('/declaration/update/{id}', [CustomDeclarationController::class, 'updateStatus'])->name('declaration.updateStatus');
Route::get('/declaration/history/{id}', [CustomDeclarationController::class, 'showHistory'])->name('declaration.showHistory');
Route::get('declaration/restore', [CustomDeclarationController::class, "showRestore"])->name("declaration.showRestore");
Route::get('dashboard/restore/{id}', [CustomDeclarationController::class, "restore"])->name("declaration.restore");

// Users CRUD (admin only)
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__ . '/auth.php';
