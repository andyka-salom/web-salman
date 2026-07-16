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
        Schema::create('action_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 255);

            // Perubahan: Mengganti duration_days dengan duration_range
            // untuk menyimpan kategori durasi seperti: <7, 8-15, 16-30, >30
            $table->string('duration_range', 15);

            // Perubahan: Kolom 'color' telah dihapus

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('priority')->default(0); // Lower = higher priority
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_categories');
    }
};
