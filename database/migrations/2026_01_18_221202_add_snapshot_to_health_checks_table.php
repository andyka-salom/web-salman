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
        Schema::table('health_checks', function (Blueprint $table) {
            // Snapshot Data (Penting agar history tetap terbaca meski crew dihapus/berubah)
            $table->string('crew_name_snapshot')
                ->nullable()
                ->after('crew_member_id');

            $table->string('crew_position_snapshot')
                ->nullable()
                ->after('crew_name_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_checks', function (Blueprint $table) {
            $table->dropColumn([
                'crew_name_snapshot',
                'crew_position_snapshot',
            ]);
        });
    }
};
