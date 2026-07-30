<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaris_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->string('foto');
            $table->tinyInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris_fotos');
    }
};
