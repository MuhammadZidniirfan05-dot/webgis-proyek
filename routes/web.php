<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
use App\Http\Controllers\MapController;

Route::middleware('auth')->group(function () {
    Route::get('/peta', [MapController::class, 'index'])->name('map.index');
    Route::get('/peta/data', [MapController::class, 'projects'])->name('map.data');
});
use App\Http\Controllers\ProjectController;

Route::middleware(['auth', 'role:admin|manajer_proyek'])->group(function () {
    Route::get('/proyek/baru', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/proyek', [ProjectController::class, 'store'])->name('projects.store');
});

Route::middleware('auth')->get('/proyek', [ProjectController::class, 'index'])->name('projects.index');

require __DIR__.'/auth.php';

