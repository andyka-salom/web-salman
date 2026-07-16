<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update crew_members table - remove vessel_id
        Schema::table('crew_members', function (Blueprint $table) {
            $table->dropForeign(['vessel_id']);
            $table->dropIndex(['vessel_id', 'is_active']);
            $table->dropColumn('vessel_id');
        });

        // Create vessel_assignments pivot table
        Schema::create('vessel_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vessel_id')->constrained('vessels')->cascadeOnDelete();
            $table->foreignUuid('crew_member_id')->constrained('crew_members')->cascadeOnDelete();
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->datetime('assigned_at')->default(now());
            $table->datetime('unassigned_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['vessel_id', 'is_active']);
            $table->index(['crew_member_id', 'is_active']);
            $table->index(['assigned_at', 'unassigned_at']);

            // PERBAIKAN: Unique constraint menggunakan unassigned_at
            // NULL values di MySQL dianggap unique, jadi hanya 1 active assignment (unassigned_at = NULL) per crew
            // Multiple inactive assignments (unassigned_at != NULL) diperbolehkan
            $table->unique(['crew_member_id', 'unassigned_at'], 'unique_active_crew_assignment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_assignments');

        Schema::table('crew_members', function (Blueprint $table) {
            $table->foreignUuid('vessel_id')->nullable()->constrained('vessels')->nullOnDelete();
            $table->index(['vessel_id', 'is_active']);
        });
    }
};
