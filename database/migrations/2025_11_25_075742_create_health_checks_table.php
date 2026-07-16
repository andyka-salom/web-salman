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
        Schema::create('health_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crew_member_id')
                ->constrained('crew_members')
                ->cascadeOnDelete();
            $table->foreignUuid('vessel_id')
                ->constrained('vessels')
                ->cascadeOnDelete();
            $table->foreignUuid('reported_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignUuid('verified_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignUuid('validated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('checked_at')->useCurrent();
            $table->date('check_date')->index();
            $table->string('work_area', 255)->nullable();

            // Medical data
            $table->text('illness_complaints');
            $table->text('medications_consumed');
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse_rate');
            $table->string('blood_pressure', 10);
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedSmallInteger('blood_sugar_level')->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->string('fatigue_level', 225)->nullable();

            // Tests
            $table->enum('napza_test_result', [
                'non_negative',
                'negative',
                'not_tested'
            ])->default('not_tested');
            $table->enum('romberg_test_result', [
                'positive',
                'negative',
                'not_tested'
            ])->default('not_tested');

            $table->text('remarks')->nullable();
            $table->enum('status', [
                'pending',
                'reviewed',
                'validated',
                'flagged'
            ])->default('pending')->index();

            // Health flags untuk quick filter
            $table->boolean('has_health_issue')->default(false)->index();
            $table->boolean('requires_follow_up')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            // Critical indexes
            $table->index(['crew_member_id', 'check_date']);
            $table->index(['vessel_id', 'check_date']);
            $table->index(['check_date', 'status']);
            $table->index(['status', 'has_health_issue']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_checks');
    }
};
