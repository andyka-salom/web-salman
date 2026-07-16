<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan nilai baru ke enum tanpa menghapus 'anomaly' terlebih dahulu
        DB::statement("ALTER TABLE cermat_reports MODIFY COLUMN classification ENUM('positive_aspect', 'anomaly', 'unsafe_condition', 'unsafe_action', 'near_miss', 'incident')");

        // 2. Migrasi data: anomaly -> unsafe_condition
        DB::table('cermat_reports')
            ->where('classification', 'anomaly')
            ->update(['classification' => 'unsafe_condition']);

        // 3. Sekarang aman untuk menghapus 'anomaly' dari definisi enum
        DB::statement("ALTER TABLE cermat_reports MODIFY COLUMN classification ENUM('positive_aspect', 'unsafe_condition', 'unsafe_action', 'near_miss', 'incident')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan data: unsafe_condition -> anomaly
        DB::table('cermat_reports')
            ->where('classification', 'unsafe_condition')
            ->update(['classification' => 'anomaly']);
            
        // Kembalikan enum (hapus unsafe_action dan ganti unsafe_condition jadi anomaly)
        DB::statement("ALTER TABLE cermat_reports MODIFY COLUMN classification ENUM('positive_aspect', 'anomaly', 'near_miss', 'incident')");
    }
};
