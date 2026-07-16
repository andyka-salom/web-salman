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
        Schema::create('entity_functions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->foreignUuid('parent_id')->nullable()
                ->constrained('entity_functions')
                ->onDelete('cascade'); // Cascade lebih aman untuk hierarchy
            $table->unsignedSmallInteger('level')->default(0)->index(); // Untuk query by level
            $table->string('path', 500)->nullable(); // Materialized path untuk query cepat
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'is_active']);
            $table->index('path'); // Untuk hierarchical queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_functions');
    }
};
