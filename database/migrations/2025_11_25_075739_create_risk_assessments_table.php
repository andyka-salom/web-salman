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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cermat_report_id')
                ->constrained('cermat_reports')
                ->cascadeOnDelete();

            $table->enum('type', ['initial', 'residual'])->index();
            $table->string('consequence_category', 100);
            $table->unsignedTinyInteger('real_severity')->nullable();
            $table->unsignedTinyInteger('potential_severity')->nullable();
            $table->unsignedTinyInteger('likelihood')->nullable();

            // Calculated risk score untuk sorting & filtering
            $table->unsignedSmallInteger('risk_score')->nullable()->index();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->nullable()->index();

            $table->timestamps();

            $table->unique(
                ['cermat_report_id', 'type', 'consequence_category'],
                'risk_assessments_unique'
            );
            $table->index(['cermat_report_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
