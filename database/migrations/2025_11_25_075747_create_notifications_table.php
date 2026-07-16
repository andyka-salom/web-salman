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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('type', 100)->index();
            $table->string('notifiable_type', 100)->nullable()->index();
            $table->uuid('notifiable_id')->nullable()->index();

            $table->string('title', 500);
            $table->text('message');
            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable()->index();

            // FIX — gunakan default
            $table->timestamp('created_at')->useCurrent()->index();

            // index gabungan
            $table->index(['user_id', 'read_at', 'created_at']);
            $table->index(['type', 'created_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
