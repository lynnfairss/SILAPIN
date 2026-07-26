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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Website Publik SILAPIN
|
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
| Admin Area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Master Data
    Route::resource('instansi', InstansiController::class);
    Route::resource('kategori', KategoriController::class);
    Route::resource('inventaris', InventarisController::class);

    // Transaksi
    Route::resource('permohonan', AdminPermohonanController::class);

});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';