<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Rahasia privat peminjam untuk membuktikan kepemilikan berkas.
     *
     * Tanpa kolom ini, /peminjam/download-surat/{id} dapat dipanggil siapa pun
     * dengan mengiterasi id berurutan dan mengambil NIK, nama, alamat, dan
     * telepon peminjam lain. Frontend peminjam menerima token ini lewat redirect
     * setelah pengajuan dan halaman cek-status; admin tidak membutuhkannya
     * karena lolos lewat sesi login.
     */
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->char('token', 64)->nullable()->after('nomor_permohonan');
        });

        DB::table('permohonans')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('permohonans')
                    ->where('id', $row->id)
                    ->update(['token' => Str::random(64)]);
            }
        });

        // Baris yang lolos backfill (mis. di-insert manual sebelum migration ini).
        DB::table('permohonans')->whereNull('token')->update(['token' => Str::random(64)]);

        Schema::table('permohonans', function (Blueprint $table) {
            $table->char('token', 64)->nullable(false)->change();
            $table->unique('token');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
        });
    }
};
