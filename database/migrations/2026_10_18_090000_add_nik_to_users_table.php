<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NIK admin/superadmin dipakai sebagai penanda tangan "Yang menyerahkan"
     * di surat, jadi harus ikut tersimpan di akun yang menyetujui.
     *
     * Disimpan sebagai string, bukan integer: NIK adalah identitas, bukan
     * angka yang dihitung. Kolom ini nullable supaya akun lama yang dibuat
     * sebelum kolom ini ada tidak ikut bentrok dengan aturan unique.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('email')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn('nik');
        });
    }
};