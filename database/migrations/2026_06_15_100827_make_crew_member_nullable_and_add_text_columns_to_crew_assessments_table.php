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
        Schema::table('crew_assessments', function (Blueprint $table) {
            $table->uuid('crew_member_id')->nullable()->change();
            $table->string('company_name_text')->nullable()->after('company_id');
            $table->string('vessel_name_text')->nullable()->after('vessel_id');
            $table->string('crew_name_text')->nullable()->after('crew_member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_assessments', function (Blueprint $table) {
            $table->uuid('crew_member_id')->nullable(false)->change();
            $table->dropColumn(['company_name_text', 'vessel_name_text', 'crew_name_text']);
        });
    }
};
