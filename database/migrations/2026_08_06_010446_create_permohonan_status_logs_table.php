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
        Schema::create('permohonan_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('permohonan_id')
                  ->constrained('permohonans')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('status_lama')->nullable();
            $table->string('status_baru');
            $table->text('catatan')->nullable();

            $table->foreignId('user_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_status_logs');
    }
};
