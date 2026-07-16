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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kontraktor', 255)->unique()->comment('Nama lengkap perusahaan');
            $table->string('sap_no', 50)->unique()->comment('Nomor SAP perusahaan. Harus unik.');
            $table->text('alamat_kantor')->nullable();
            $table->string('alamat_email', 100)->unique()->nullable()->comment('Alamat email kantor utama');
            $table->string('no_tlp_kantor', 50)->nullable();
            $table->timestamps();
            $table->index('nama_kontraktor');
            $table->index('sap_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
