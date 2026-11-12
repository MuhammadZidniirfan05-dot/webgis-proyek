<?php

use App\Http\Controllers\Auth\AdminAccessController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

    // Khusus admin: audit log, wilayah, pengguna
    Route::middleware('role:admin')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        Route::resource('wilayah', RegionController::class)
            ->parameters(['wilayah' => 'region'])
            ->except(['show', 'create'])
            ->names('regions');

        Route::resource('pengguna', UserController::class)
            ->parameters(['pengguna' => 'user'])
            ->except(['show'])
            ->names('users');
    });

    // Proyek: daftar dan ekspor
    Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/proyek-ekspor/excel', [ProjectController::class, 'exportExcel'])->name('projects.export.excel');
    Route::get('/proyek-ekspor/pdf', [ProjectController::class, 'exportPdf'])->name('projects.export.pdf');

    // Proyek: admin dan manajer. /proyek/baru harus di atas /proyek/{project}
    Route::middleware('role:admin|manajer_proyek')->group(function () {
        Route::get('/proyek/baru', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/proyek', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/proyek/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/proyek/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/proyek/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    Route::get('/proyek/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Laporan harian
    Route::post('/proyek/{project}/laporan', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/proyek/{project}/laporan/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/proyek/{project}/laporan/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('/proyek/{project}/laporan/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
});

// Masuk sekali klik per role (manajer, petugas, klien)
Route::post('/demo-login/{role}', DemoLoginController::class)
    ->middleware(['guest', 'throttle:20,1'])
    ->name('demo.login');

// Akses admin lewat izin email
Route::middleware('guest')->group(function () {
    Route::get('/minta-akses-admin', [AdminAccessController::class, 'form'])->name('admin-access.form');
    Route::post('/minta-akses-admin', [AdminAccessController::class, 'store'])
        ->middleware('throttle:3,60')->name('admin-access.store');
    Route::get('/minta-akses-admin/menunggu', [AdminAccessController::class, 'waiting'])->name('admin-access.waiting');
    Route::get('/minta-akses-admin/status', [AdminAccessController::class, 'status'])->name('admin-access.status');
    Route::post('/minta-akses-admin/masuk', [AdminAccessController::class, 'claim'])->name('admin-access.claim');
});

// Tautan di emailmu (bertanda tangan; boleh dibuka walau sedang login)
Route::get('/akses-admin/{accessRequest}', [AdminAccessController::class, 'review'])
    ->middleware('signed')->name('admin-access.review');
Route::post('/akses-admin/{accessRequest}', [AdminAccessController::class, 'decide'])
    ->middleware('signed');

require __DIR__.'/auth.php';