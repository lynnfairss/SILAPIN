<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->foreignId('jenis_id')->nullable()->after('nama_barang')
                ->constrained('jensis')->nullOnDelete();
        });

        // Migrasi data: buat Jenis dari nilai unik jenis_barang lalu samakan id-nya
        $rows = DB::table('inventaris')
            ->whereNotNull('jenis_barang')
            ->where('jenis_barang', '!=', '')
            ->select('jenis_barang')
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            $nama = trim($row->jenis_barang);
            if ($nama === '') {
                continue;
            }
            $jenisId = DB::table('jensis')->insertGetId([
                'nama_jenis' => $nama,
                'keterangan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('inventaris')
                ->where('jenis_barang', $row->jenis_barang)
                ->update(['jenis_id' => $jenisId]);
        }

        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropColumn('jenis_barang');
        });
    }

    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->string('jenis_barang', 150)->nullable()->after('nama_barang');
        });

        // Kembalikan nilai teks dari relasi jenis
        DB::table('inventaris')
            ->leftJoin('jensis', 'inventaris.jenis_id', '=', 'jensis.id')
            ->whereNotNull('jensis.nama_jenis')
            ->update(['inventaris.jenis_barang' => DB::raw('jensis.nama_jenis')]);

        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropForeign(['jenis_id']);
            $table->dropColumn('jenis_id');
        });
    }
};
