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
        Schema::create('inventaris', function (Blueprint $table) {

            $table->id();

            // Relasi ke tabel kategori
            $table->foreignId('kategori_id')
                  ->constrained('kategoris')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            // Data inventaris
            $table->string('kode_barang', 30)->unique();
            $table->string('nama_barang', 150);
            $table->integer('stok')->default(0);

            $table->enum('kondisi', [
                'Baik',
                'Rusak Ringan',
                'Rusak Berat'
            ])->default('Baik');

            $table->text('deskripsi')->nullable();

            // Foto barang
            $table->string('foto')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};