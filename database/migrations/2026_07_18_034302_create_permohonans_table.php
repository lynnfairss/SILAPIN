<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permohonans', function (Blueprint $table) {
            $table->id();

            // Instansi peminjam
            $table->foreignId('instansi_id')
                  ->constrained('instansis')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // Data peminjam
            $table->string('nama_peminjam', 150);
            $table->string('nik', 20);
            $table->string('jabatan', 100)->nullable();
            $table->string('telepon', 20);

            // Tanggal peminjaman
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali');

            // Keperluan
            $table->text('keperluan');

            // Status permohonan
            $table->enum('status', [
                'Menunggu',
                'Disetujui',
                'Ditolak',
                'Dipinjam',
                'Dikembalikan'
            ])->default('Menunggu');

            // Catatan admin
            $table->text('catatan_admin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonans');
    }
};