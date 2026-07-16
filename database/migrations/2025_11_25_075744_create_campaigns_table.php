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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 500);
            $table->string('slug', 500)->unique();
            $table->string('media', 500)->nullable();
            $table->enum('media_type', ['image'])->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->dateTime('published_at')->nullable()->index();
            $table->string('category', 100)->nullable()->index();

            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'published_at']);
            $table->index(['category', 'is_published']);
            $table->fullText(['title', 'summary']); // Untuk searching
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
