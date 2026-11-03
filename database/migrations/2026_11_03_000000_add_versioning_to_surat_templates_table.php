<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu tahun sekarang boleh punya banyak versi template.
     *
     * Template lama tidak dihapus: hanya ditandai tidak aktif. File fisiknya
     * tetap tersimpan supaya surat lama masih bisa memakainya.
     */
    public function up(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropUnique(['tahun']);
        });

        Schema::table('surat_templates', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->unsignedSmallInteger('versi')->default(1)->after('tahun');
            $table->boolean('is_active')->default(false)->after('versi');
            $table->string('hash', 64)->nullable()->after('path');
            $table->unsignedBigInteger('ukuran')->nullable()->after('hash');
            $table->string('catatan')->nullable()->after('ukuran');
            $table->softDeletes()->after('updated_at');

            $table->index(['tahun', 'is_active']);
        });

        // Data lama: satu template per tahun, semuanya dianggap aktif.
        DB::table('surat_templates')->update(['is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropIndex(['tahun', 'is_active']);
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'versi', 'is_active', 'hash', 'ukuran', 'catatan']);
        });

        Schema::table('surat_templates', function (Blueprint $table) {
            $table->unique('tahun');
        });
    }
};
