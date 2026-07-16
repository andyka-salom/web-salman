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
        Schema::create('cermat_report_unsafe_condition', function (Blueprint $table) {
            $table->foreignUuid('cermat_report_id')
                ->constrained('cermat_reports')
                ->cascadeOnDelete();
            $table->foreignUuid('unsafe_condition_id')
                ->constrained('unsafe_conditions')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['cermat_report_id', 'unsafe_condition_id'], 'cr_uc_primary');
            $table->index('unsafe_condition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cermat_report_unsafe_condition');
    }
};
