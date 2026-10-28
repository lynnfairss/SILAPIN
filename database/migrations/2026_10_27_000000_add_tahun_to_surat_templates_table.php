<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel surat_templates dulu dibuat untuk menyimpan template dalam bentuk
     * JSON, tetapi tidak pernah dipakai. Sekarang tabel yang sama dipakai
     * untuk menyimpan template DOCX per tahun (2025, 2026, dst).
     */
    public function up(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->nullable()->after('id');
            $table->string('nama_file')->nullable()->after('tahun');
            $table->string('path')->nullable()->after('nama_file');
            $table->foreignId('uploaded_by')->nullable()->after('path')
                ->constrained('users')->nullOnDelete();

            // Satu template per tahun. Indeks terpisah agar tetap aman di
            // SQLite (yang tidak bisa menambah kolom UNIQUE lewat ALTER TABLE).
            $table->unique('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropUnique(['tahun']);
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['tahun', 'nama_file', 'path', 'uploaded_by']);
        });
    }
};
