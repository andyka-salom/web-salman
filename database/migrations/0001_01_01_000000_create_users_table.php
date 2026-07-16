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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255)->index();
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->string('phone', 20)->index();
            $table->string('jabatan', 255)->nullable()->index();
            $table->string('photo_path', 500)->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable()->index();
            $table->ipAddress('last_login_ip')->nullable();
            $table->unsignedTinyInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys dengan index otomatis
            $table->foreignUuid('company_id')->nullable()
                ->constrained('companies')
                ->onDelete('set null');

            $table->foreignUuid('entity_function_id')->nullable()
                ->constrained('entity_functions')
                ->onDelete('set null');

            // Composite indexes untuk query umum
            $table->index(['company_id', 'is_active']);
            $table->index(['entity_function_id', 'is_active']);
            $table->index(['email_verified_at', 'is_active']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
