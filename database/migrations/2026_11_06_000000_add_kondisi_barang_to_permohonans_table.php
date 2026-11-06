<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kondisi fisik barang saat dikembalikan. Dipisah dari catatan_admin
     * supaya bisa ditampilkan sebagai badge dan direkap tanpa parsing teks.
     */
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('kondisi_barang', 30)->nullable()->after('bukti_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn('kondisi_barang');
        });
    }
};
