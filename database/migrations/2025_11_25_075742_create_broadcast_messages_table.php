<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama broadcast messages
        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title', 500)->nullable();
            $table->text('message');

            // Tipe target broadcast
            $table->enum('target_type', [
                'manual',           // Input manual nomor
                'group_contact',    // Dari contact group
                'group_wa',         // Dari WhatsApp group
            ])->index();

            $table->json('target_data')->nullable(); // Store IDs/phones

            // Status broadcast
            $table->enum('status', [
                'draft',
                'scheduled',
                'processing',
                'sending',
                'completed',
                'failed',
                'cancelled'
            ])->default('draft')->index();

            // Scheduling
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Media attachment
            $table->string('media_type', 50)->nullable(); // image, video, document
            $table->text('media_url')->nullable();
            $table->string('media_filename', 255)->nullable();

            // Statistics (denormalized for performance)
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);
            $table->decimal('success_rate', 5, 2)->nullable();

            // API Response tracking
            $table->json('api_response')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes
            $table->index(['created_by', 'status', 'created_at']);
            $table->index(['scheduled_at', 'status']);
        });

        // Tabel detail recipients per broadcast
        Schema::create('broadcast_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('broadcast_message_id')
                ->constrained('broadcast_messages')
                ->cascadeOnDelete();

            // Recipient info
            $table->enum('recipient_type', ['user', 'crew_member', 'manual', 'wa_group']);
            $table->uuid('recipient_id')->nullable(); // ID user/crew jika ada
            $table->string('phone_number', 20)->index();
            $table->string('recipient_name', 255)->nullable();

            // Delivery status per recipient
            $table->string('status', 20)->default('pending')->index();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            // Error tracking per recipient
            $table->text('error_message')->nullable();
            $table->json('api_response')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['broadcast_message_id', 'status']);
            $table->index(['phone_number', 'sent_at']);
        });

        // Cache WhatsApp Groups
        Schema::create('whatsapp_groups_cache', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('group_id', 255)->unique(); // Fonnte group ID
            $table->string('group_name', 255);
            $table->json('members')->nullable(); // Store member list
            $table->unsignedInteger('member_count')->default(0);
            $table->timestamp('last_synced_at')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_recipients');
        Schema::dropIfExists('broadcast_messages');
        Schema::dropIfExists('whatsapp_groups_cache');
    }
};
