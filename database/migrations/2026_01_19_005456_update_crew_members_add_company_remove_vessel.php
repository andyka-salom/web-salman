<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {

            // ADD company_id (nullable)
            $table->foreignUuid('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {

            // rollback company_id
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');

            // rollback vessel_id (optional)
            $table->foreignUuid('vessel_id')
                ->nullable()
                ->constrained('vessels')
                ->nullOnDelete();
        });
    }
};
