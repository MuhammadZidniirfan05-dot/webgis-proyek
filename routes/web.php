<?php

use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Peta
    Route::get('/peta', [MapController::class, 'index'])->name('map.index');
    Route::get('/peta/data', [MapController::class, 'projects'])->name('map.data');

    // Proyek (semua yang login boleh melihat)
    Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');

    // Proyek (khusus admin dan manajer). /proyek/baru harus di atas /proyek/{project}
    Route::middleware('role:admin|manajer_proyek')->group(function () {
        Route::get('/proyek/baru', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/proyek', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/proyek/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/proyek/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/proyek/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    Route::get('/proyek/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Laporan harian (hak akses ditegakkan di controller dan policy)
    Route::post('/proyek/{project}/laporan', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/proyek/{project}/laporan/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/proyek/{project}/laporan/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('/proyek/{project}/laporan/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
});

require __DIR__.'/auth.php';