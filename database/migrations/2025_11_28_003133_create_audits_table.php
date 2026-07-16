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
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->create($table, function (Blueprint $table) {

            // Kolom ID utama tetap bigIncrements
            $table->bigIncrements('id');

            // --- PERUBAHAN UNTUK USER (user_id & user_type) ---
            // Mengganti unsignedBigInteger menjadi uuid
            $table->uuid('user_id')->nullable();
            $table->string('user_type')->nullable();

            $table->string('event');

            // --- PERUBAHAN UNTUK AUDITABLE (auditable_id & auditable_type) ---
            // Mengganti morphs('auditable') yang default-nya integer
            // dengan definisi UUID secara eksplisit.
            $table->uuid('auditable_id');
            $table->string('auditable_type');

            // Kolom-kolom lainnya tetap sama
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            // Index juga disesuaikan untuk kolom yang baru
            $table->index(['user_id', 'user_type']);
            $table->index(['auditable_id', 'auditable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->drop($table);
    }
};
