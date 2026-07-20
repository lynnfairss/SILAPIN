<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_permohonans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('permohonan_id')
                  ->constrained('permohonans')
                  ->onDelete('cascade');

            $table->foreignId('inventaris_id')
                  ->constrained('inventaris')
                  ->onDelete('cascade');

            $table->integer('jumlah');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_permohonans');
    }
};