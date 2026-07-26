<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstansiController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\PermohonanController as AdminPermohonanController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Website Publik
|--------------------------------------------------------------------------
*/

Route::get('/', [WebsiteController::class, 'index'])->name('website');

/*
|--------------------------------------------------------------------------
| Peminjam (Publik)
|--------------------------------------------------------------------------
*/

Route::prefix('peminjam')->name('peminjam.')->group(function () {
    Route::get('form', [PermohonanController::class, 'form'])->name('form');
    Route::post('store', [PermohonanController::class, 'store'])->name('store');
    Route::get('cek-status', [PermohonanController::class, 'cekStatus'])->name('cek-status');
    Route::get('download-surat/{permohonan}', [PermohonanController::class, 'downloadSurat'])->name('download-surat');
});

/*
|--------------------------------------------------------------------------
| Admin Area (Semua role yang sudah login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard (semua role)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Master Data (hanya Super Admin)
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('instansi', InstansiController::class)->except(['create', 'edit']);
        Route::resource('kategori', KategoriController::class)->except(['create', 'edit']);
        Route::resource('inventaris', InventarisController::class)->except(['create', 'edit']);

        // Manajemen User (hanya Super Admin)
        Route::resource('users', UserController::class)->except(['create', 'store', 'edit', 'update']);
    });

    // Permohonan (Super Admin + Admin)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('permohonan', AdminPermohonanController::class);
        Route::patch('permohonan/{permohonan}/status', [AdminPermohonanController::class, 'updateStatus'])
            ->name('permohonan.status');
    });

});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
