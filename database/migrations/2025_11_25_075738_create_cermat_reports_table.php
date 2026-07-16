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
        Schema::create('cermat_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('report_number', 100)->unique();

            // Foreign keys
            $table->foreignUuid('area_id')->constrained('areas');
            $table->foreignUuid('reporter_id')->constrained('users');
            $table->foreignUuid('line_supervisor_id')->nullable()->constrained('users');
            $table->foreignUuid('hsse_officer_id')->nullable()->constrained('users');
            $table->foreignUuid('close_out_performer_id')->nullable()->constrained('users');
            $table->foreignUuid('pelanggaran_id')->nullable()->constrained('pelanggaran');
            $table->foreignUuid('security_event_category_id')->nullable()
                ->constrained('security_event_categories');

            // Core fields
            $table->text('details');
            $table->decimal('value_of_loss', 15, 2)->nullable()->index();
            $table->dateTime('report_datetime')->index();
            $table->text('immediate_action_taken');

            // Classification
            // $table->boolean('is_limited_circulation')->default(false)->index();
            $table->enum('classification', [
                'positive_aspect',
                'anomaly',
                'near_miss',
                'incident'
            ])->nullable()->index();
            $table->enum('event_type', ['hse', 'security'])->default('hse')->index();
            $table->string('location_details', 500)->nullable();

            // Financial
            $table->string('synergi_register_no', 100)->nullable()->index();
            $table->decimal('cost_to_business_actual', 15, 2)->nullable();
            $table->decimal('cost_to_business_potential', 15, 2)->nullable();

            /**
             * ================================
             *  STATUS BARU (PARALEL WORKFLOW)
             * ================================
             */

            // Status supervisor - tidak memblokir HSSE
            $table->enum('supervisor_status', [
                'awaiting_approval',
                'approved',
                'rejected',
                'not_required'
            ])->default('awaiting_approval')->index();

            // Status HSSE workflow
            $table->enum('hsse_status', [
                'pending_review',
                'reviewed',
                'recommended'
            ])->default('pending_review')->index();

            // Status global (alur utama)
            $table->enum('status', [
                'draft',
                'in_review',            // HSSE bisa review walau supervisor belum approve
                'action_in_progress',
                'awaiting_closeout',
                'closed',
                'no_action_required'    // quick close
            ])->default('draft')->index();

            // $table->boolean('notified_line_management')->default(false)->index();
            $table->boolean('stop_card_issued')->default(false)->index();
            $table->boolean('requires_feedback')->default(false)->index();

            // Notes & improvements
            $table->text('short_term_mitigation')->nullable();
            $table->text('supervisor_notes')->nullable();
            $table->text('suggestion_for_improvement')->nullable();
            $table->json('reported_event_categories')->nullable();

            // Timestamps for workflow tracking
            $table->date('internal_deadline')->nullable()->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->timestamp('supervisor_rejected_at')->nullable();
            $table->timestamp('hsse_reviewed_at')->nullable();
            $table->timestamp('hsse_recommended_at')->nullable();
            $table->timestamp('close_out_at')->nullable();

            $table->text('close_out_description')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes
            $table->index(['status', 'report_datetime']);
            $table->index(['area_id', 'status', 'report_datetime']);
            $table->index(['reporter_id', 'status']);
            $table->index(['event_type', 'classification', 'status']);
            $table->index(['internal_deadline', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cermat_reports');
    }
};
