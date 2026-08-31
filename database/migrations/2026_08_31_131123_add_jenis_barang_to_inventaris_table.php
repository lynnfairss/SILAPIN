<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            if (! Schema::hasColumn('inventaris', 'jenis_barang')) {
                $table->string('jenis_barang', 150)->nullable()->after('nama_barang');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            if (Schema::hasColumn('inventaris', 'jenis_barang')) {
                $table->dropColumn('jenis_barang');
            }
        });
    }
};
