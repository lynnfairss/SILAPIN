<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan template yang dipakai oleh masing-masing surat.
     *
     * Dengan begitu surat lama tetap memakai template lamanya walaupun admin
     * sudah mengunggah template baru untuk tahun yang sama.
     */
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->foreignId('surat_template_id')->nullable()->after('word_path')
                ->constrained('surat_templates')->nullOnDelete();
        });

        // Backfill surat lama berdasarkan tahun surat dibuat.
        DB::table('permohonans')
            ->select('id', 'created_at')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $tahun = (int) date('Y', strtotime((string) $row->created_at));

                    $templateId = DB::table('surat_templates')
                        ->where('tahun', $tahun)
                        ->orderByDesc('is_active')
                        ->orderByDesc('versi')
                        ->value('id');

                    if ($templateId) {
                        DB::table('permohonans')
                            ->where('id', $row->id)
                            ->update(['surat_template_id' => $templateId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('surat_template_id');
        });
    }
};
