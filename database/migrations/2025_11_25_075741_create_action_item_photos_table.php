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
        Schema::create('action_item_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('action_item_id')
                ->constrained('action_items')
                ->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable(); // bytes
            $table->enum('photo_type', ['before', 'progress', 'after'])->default('progress');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['action_item_id', 'photo_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_item_photos');
    }
};
