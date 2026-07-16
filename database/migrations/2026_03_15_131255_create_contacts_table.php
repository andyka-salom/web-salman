<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name', 255);
            $table->string('whatsapp_number', 20);
            $table->string('position', 255)->nullable();  // Jabatan/Posisi
            $table->text('notes')->nullable();             // Keterangan

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['name', 'whatsapp_number']);
            $table->index(['created_by', 'is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
