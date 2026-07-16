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
        Schema::create('cover_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('cover_image_path', 500);
            $table->string('page_image_path', 500);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes(); // Ini sudah benar
        });

        Schema::create('campaign_salman', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->string('tema');
            $table->string('lokasi');
            $table->integer('jumlah_peserta');
            $table->string('pemateri');
            $table->string('entitas');
            $table->longText('ringkasan');
            $table->json('dokumentasi')->nullable();
            $table->json('daftar_hadir')->nullable();

            $table->foreignUuid('cover_template_id')
                ->nullable()
                ->constrained('cover_templates')
                ->nullOnDelete();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // --- TAMBAHKAN BARIS INI ---
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jangan lupa drop kedua tabel saat rollback
        Schema::dropIfExists('campaign_salman');
        Schema::dropIfExists('cover_templates');
    }
};
