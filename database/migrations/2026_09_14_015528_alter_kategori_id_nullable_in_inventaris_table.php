<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan ulang karena versi lama memakai SQL MySQL mentah
     * (ALTER TABLE ... MODIFY COLUMN) yang tidak dimengerti SQLite maupun
     * PostgreSQL. phpunit.xml memakai sqlite :memory:, sehingga seluruh
     * feature test gagal pada tahap migrasi sebelum satu pun test berjalan.
     */
    public function up(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable(false)->change();
        });
    }
};
