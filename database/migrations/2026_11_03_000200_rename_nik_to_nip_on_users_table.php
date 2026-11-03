<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Akun Admin/Super Admin memakai NIP, bukan NIK. Nilai lama ikut
     * dipindahkan apa adanya lewat rename kolom.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nik', 'nip');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 18)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('nip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nip']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 16)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nip', 'nik');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('nik');
        });
    }
};
