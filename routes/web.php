<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstansiController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\PermohonanController;

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
    Route::resource('permohonan', PermohonanController::class);

});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';