<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->string('logo_kiri')->nullable()->after('template');
            $table->string('logo_kanan')->nullable()->after('logo_kiri');
        });
    }

    public function down(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropColumn(['logo_kiri', 'logo_kanan']);
        });
    }
};
