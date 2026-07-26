<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('nomor_permohonan', 50)->unique()->after('id');
            $table->string('nama_instansi_lain', 100)->nullable()->after('instansi_id');
            $table->string('foto_ktp')->nullable()->after('catatan_admin');
            $table->string('surat_tugas')->nullable()->after('foto_ktp');
            $table->string('bukti_pengambilan')->nullable()->after('surat_tugas');
            $table->string('bukti_pengembalian')->nullable()->after('bukti_pengambilan');
            $table->foreignId('instansi_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_permohonan',
                'nama_instansi_lain',
                'foto_ktp',
                'surat_tugas',
                'bukti_pengambilan',
                'bukti_pengembalian',
            ]);
        });
    }
};
