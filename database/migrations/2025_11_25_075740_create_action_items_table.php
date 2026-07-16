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
        Schema::create('action_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cermat_report_id')
                ->constrained('cermat_reports')
                ->cascadeOnDelete();
            $table->foreignUuid('responsible_id')
                ->constrained('users');
            $table->foreignUuid('action_category_id')->nullable()
                ->constrained('action_categories')
                ->onDelete('set null');

            $table->text('description');
            $table->date('target_date')->index();
            $table->date('current_target_date')->index(); // Untuk query deadline

            // Extension tracking (normalized)
            $table->unsignedTinyInteger('extension_count')->default(0);
            $table->date('extend_date_1')->nullable();
            $table->text('extend_reason_1')->nullable();
            $table->date('extend_date_2')->nullable();
            $table->text('extend_reason_2')->nullable();
            $table->date('extend_date_3')->nullable();
            $table->text('extend_reason_3')->nullable();

            $table->enum('status', [
                'do',
                'in_progress',
                'completed',
                'cannot_do',
                'overdue'
            ])->default('do')->index();

            $table->text('completion_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_overdue')->default(false)->index(); // Computed field

            $table->timestamps();
            $table->softDeletes();

            // Critical indexes
            $table->index(['responsible_id', 'status']);
            $table->index(['cermat_report_id', 'status']);
            $table->index(['current_target_date', 'status']);
            $table->index(['status', 'is_overdue', 'current_target_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_items');
    }
};
