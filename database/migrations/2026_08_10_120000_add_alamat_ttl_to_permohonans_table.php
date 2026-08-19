<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('telepon');
            $table->string('tempat_tanggal_lahir', 120)->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'tempat_tanggal_lahir']);
        });
    }
};
