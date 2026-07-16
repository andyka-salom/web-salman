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
        Schema::table('vessels', function (Blueprint $table) {
            // Drop code column
            $table->dropUnique(['code']); // Drop unique constraint first
            $table->dropColumn('code');

            // Make type required (not nullable)
            $table->string('type', 100)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vessels', function (Blueprint $table) {
            // Add code column back
            $table->string('code', 50)->unique()->after('name');

            // Make type nullable again
            $table->string('type', 100)->nullable()->change();
        });
    }
};
