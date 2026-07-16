<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('crew_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // WAJIB sistem - tidak bisa manual
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('vessel_id')->constrained('vessels')->cascadeOnDelete();
            $table->foreignUuid('crew_member_id')->constrained('crew_members')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            // Kolom B: No. Sertifikat (beda dari COC, bisa kosong)
            $table->string('certificate_number')->nullable();
            // Kolom E: COC (ANT IV M / ATT V M)
            $table->string('coc')->nullable();
            // Kolom F-G: Jabatan
            $table->string('position_proposed')->nullable();
            $table->string('position_type')->nullable();
            // Kolom I-K: Pengalaman
            $table->string('experience_pertamina')->nullable();
            $table->string('experience_master')->nullable();
            $table->string('experience_outside')->nullable();
            // Kolom L: MEV
            $table->string('mev_type')->nullable();
            // Kolom M: Tanggal + Lokasi
            $table->date('assessment_date');
            $table->string('assessment_location')->nullable();
            // Kolom N-P: Assessor
            $table->string('assessor_mar')->nullable();
            $table->string('assessor_hse')->nullable();
            $table->string('assessor_fmc')->nullable();
            // Kolom Q: HASIL, R: KETERANGAN
            $table->string('result')->nullable();
            $table->text('notes')->nullable();
            // Extra validitas
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->enum('status', ['active','expired','revoked'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id','assessment_date']);
            $table->index(['vessel_id','assessment_date']);
            $table->index(['crew_member_id','assessment_date']);
            $table->index('result');
            $table->index('mev_type');
            $table->index('assessment_date');
            $table->index('certificate_number');
        });

        Schema::create('crew_assessment_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crew_assessment_id')->constrained('crew_assessments')->cascadeOnDelete();
            $table->foreignUuid('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->index('crew_assessment_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('crew_assessment_attachments');
        Schema::dropIfExists('crew_assessments');
    }
};
