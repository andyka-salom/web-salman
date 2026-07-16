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
        Schema::create('cermat_report_unsafe_act', function (Blueprint $table) {
            $table->foreignUuid('cermat_report_id')
                ->constrained('cermat_reports')
                ->cascadeOnDelete();
            $table->foreignUuid('unsafe_act_id')
                ->constrained('unsafe_acts')
                ->cascadeOnDelete();
            $table->timestamps(); // Untuk audit trail

            $table->primary(['cermat_report_id', 'unsafe_act_id'], 'cr_ua_primary');
            $table->index('unsafe_act_id'); // Untuk reverse lookup
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cermat_report_unsafe_act');
    }
};
