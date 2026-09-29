<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cegah penghapusan master data merusak riwayat peminjaman.
     *
     * Sebelum migration ini:
     *  - permohonans.instansi_id = ON DELETE CASCADE. Menghapus satu baris
     *    Instansi ikut menghapus semua permohonannya, termasuk yang sedang
     *    Dipinjam, beserta detail dan status log-nya.
     *  - detail_permohonans.inventaris_id = ON DELETE CASCADE. Menghapus satu
     *    baris Inventaris menghapus detail barang dari peminjaman aktif, lalu
     *    saat pengembalian stok yang dikembalikan menjadi nol dan hilang.
     */
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);

            $table->foreign('instansi_id')
                ->references('id')
                ->on('instansis')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('detail_permohonans', function (Blueprint $table) {
            $table->dropForeign(['inventaris_id']);

            $table->foreign('inventaris_id')
                ->references('id')
                ->on('inventaris')
                ->restrictOnDelete();
        });

        // Seluruh query dashboard, daftar permohonan, dan filter status memakai kolom ini.
        Schema::table('permohonans', function (Blueprint $table) {
            $table->index('status');
            $table->index('tanggal_pinjam');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal_pinjam']);
        });

        Schema::table('detail_permohonans', function (Blueprint $table) {
            $table->dropForeign(['inventaris_id']);

            $table->foreign('inventaris_id')
                ->references('id')
                ->on('inventaris')
                ->cascadeOnDelete();
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);

            $table->foreign('instansi_id')
                ->references('id')
                ->on('instansis')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
