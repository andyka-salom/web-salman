<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            $table->date('mcu_valid_until')->nullable()->after('phone')
                  ->comment('Tanggal masa aktif Medical Check-Up');
            $table->enum('health_category', ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7'])
                  ->nullable()->after('mcu_valid_until')
                  ->comment('Kategori kesehatan hasil MCU');

            $table->index('mcu_valid_until');
            $table->index('health_category');
        });
    }

    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            $table->dropIndex('crew_members_mcu_valid_until_index');
            $table->dropIndex('crew_members_health_category_index');
            $table->dropColumn(['mcu_valid_until', 'health_category']);
        });
    }
};
