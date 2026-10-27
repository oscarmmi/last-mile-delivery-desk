<?php

use App\Http\Controllers\DispatcherController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StopController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DispatcherController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('drivers', DriverController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('stops', [StopController::class, 'store'])->name('stops.store');
    Route::patch('stops/{stop}/assignment', [StopController::class, 'assign'])->name('stops.assign');
    Route::patch('stops/{stop}/status', [StopController::class, 'updateStatus'])->name('stops.status');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
