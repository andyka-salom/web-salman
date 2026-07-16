<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HSSE Marine Onboard Evaluation — All Tables
 *
 * Membuat 3 tabel sekaligus:
 *  1. hsse_criteria          → Master 5 aspek penilaian
 *  2. hsse_evaluations       → Form utama per sesi evaluasi kru
 *  3. hsse_evaluation_scores → Detail nilai per aspek per evaluasi
 *
 * Skala penilaian : 1 = Kurang | 2 = Cukup | 3 = Baik
 * Kategori score  : 5–8 = Kurang | 9–11 = Cukup | 12–15 = Baik
 */
return new class extends Migration
{
    // ─────────────────────────────────────────────────────────────────
    // UP
    // ─────────────────────────────────────────────────────────────────
    public function up(): void
    {
        // ── 1. Master Aspek Penilaian ─────────────────────────────────
        Schema::create('hsse_criteria', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Urutan tampil di form (No 1–5)
            $table->unsignedTinyInteger('order_no')->unique();

            // Deskripsi aspek yang dinilai
            $table->text('aspect');

            // Soft-toggle jika aspek perlu dinonaktifkan tanpa hapus histori
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();
        });

        // ── 2. Form Utama Evaluasi ────────────────────────────────────
        Schema::create('hsse_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ── Relasi konteks ────────────────────────────────────────
            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignUuid('vessel_id')
                ->constrained('vessels')
                ->restrictOnDelete();

            // Kru yang dievaluasi — nullable karena bisa input manual
            $table->foreignUuid('crew_member_id')
                ->nullable()
                ->constrained('crew_members')
                ->nullOnDelete();

            // Snapshot nama & jabatan saat evaluasi
            // (antisipasi data kru berubah di masa depan)
            $table->string('crew_name', 255);
            $table->string('crew_position', 255)->nullable();

            // ── Tanggal ───────────────────────────────────────────────
            $table->date('evaluated_date')->index();

            // ── Hasil Penilaian ───────────────────────────────────────
            // Dikalkulasi otomatis via Observer dari hsse_evaluation_scores
            $table->unsignedTinyInteger('total_score')
                ->nullable()
                ->comment('Range 5–15; SUM dari hsse_evaluation_scores');

            // Kategori otomatis berdasar total_score
            $table->enum('score_category', ['kurang', 'cukup', 'baik'])
                ->nullable()
                ->index()
                ->comment('kurang=5-8 | cukup=9-11 | baik=12-15');

            // ── Catatan / Rekomendasi ─────────────────────────────────
            $table->text('notes')->nullable();

            // ── Assessor ──────────────────────────────────────────────
            $table->foreignUuid('assessor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Snapshot assessor (nama & jabatan bisa berubah di masa depan)
            $table->string('assessor_name', 255)->nullable();
            $table->string('assessor_position', 255)->nullable();

            // Path file tanda tangan
            // $table->string('assessor_signature_path', 500)->nullable();

            // ── Status Form ───────────────────────────────────────────
            // draft     → belum submit, belum masuk dashboard
            // submitted → final, terhitung di dashboard
            $table->enum('status', ['draft', 'submitted'])
                ->default('draft')
                ->index();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
            // $table->softDeletes();

            // ── Composite Indexes ─────────────────────────────────────
            $table->index(['company_id', 'evaluated_date']);   // filter dashboard per perusahaan + periode
            $table->index(['vessel_id', 'evaluated_date']);    // filter per kapal + periode
            $table->index(['vessel_id', 'score_category']);    // agregasi kompetensi per kapal
            $table->index(['company_id', 'score_category']);   // agregasi kompetensi per perusahaan
            $table->index(['evaluated_date', 'status']);       // query submitted dalam rentang tanggal
            $table->index(['crew_member_id', 'evaluated_date']); // riwayat evaluasi per kru
        });

        // ── 3. Detail Nilai per Aspek ─────────────────────────────────
        Schema::create('hsse_evaluation_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('evaluation_id')
                ->constrained('hsse_evaluations')
                ->cascadeOnDelete(); // hapus scores jika evaluasi dihapus

            $table->foreignUuid('criteria_id')
                ->constrained('hsse_criteria')
                ->restrictOnDelete(); // jangan hapus kriteria jika masih ada nilai

            // 1 = Kurang | 2 = Cukup | 3 = Baik
            $table->unsignedTinyInteger('score')
                ->comment('1=Kurang | 2=Cukup | 3=Baik');

            // Keterangan opsional per aspek (kolom Keterangan di form)
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Satu evaluasi tidak boleh punya 2 nilai untuk kriteria yang sama
            $table->unique(['evaluation_id', 'criteria_id']);

            // Index untuk query agregasi dashboard
            $table->index(['criteria_id', 'score']);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // DOWN — drop urutan terbalik (child dulu, parent belakangan)
    // ─────────────────────────────────────────────────────────────────
    public function down(): void
    {
        Schema::dropIfExists('hsse_evaluation_scores');
        Schema::dropIfExists('hsse_evaluations');
        Schema::dropIfExists('hsse_criteria');
    }
};
