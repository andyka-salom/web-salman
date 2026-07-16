<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration lengkap KPI HSSE Kontraktor — Production v3
 * Semua tabel dalam 1 file, urutan foreign-key-safe.
 *
 * Tabel yang dibuat:
 *  1. kpi_periods               — periode laporan (bulan/tahun)
 *  2. kpi_items                 — master item KPI (lagging & leading)
 *  3. kpi_reports               — header laporan per perusahaan per periode
 *  4. kpi_report_vessels        — data kapal / unit per laporan
 *  5. kpi_report_details        — detail per item KPI per laporan
 *  6. kpi_report_detail_evidences — lampiran bukti per detail item
 *  7. kpi_report_item_reviews   — review/verifikasi HSSE per item
 *  8. kpi_report_status_logs    — audit trail perubahan status laporan
 *
 * Asumsi:
 *  - Tabel `users` dan `companies` sudah ada (dari project utama)
 *  - UUID digunakan sebagai primary key di semua tabel KPI
 *  - Engine InnoDB + charset utf8mb4
 */
return new class extends Migration {
    // ── Urutan drop harus kebalikan dari create (FK safe) ────────────────
    private array $tables = [
        'kpi_report_status_logs',
        'kpi_report_item_reviews',
        'kpi_report_detail_evidences',
        'kpi_report_details',
        'kpi_report_vessels',
        'kpi_reports',
        'kpi_items',
        'kpi_periods',
    ];

    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════
        // 1. KPI PERIODS
        //    Menyimpan periode laporan (kombinasi unik bulan + tahun).
        //    Mengontrol window pengisian (submission_start – submission_end).
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedTinyInteger('period_month')
                ->comment('1–12');
            $table->unsignedSmallInteger('period_year')
                ->comment('Contoh: 2025');

            $table->date('submission_start')->nullable()
                ->comment('Awal window pengisian laporan');
            $table->date('submission_end')->nullable()
                ->comment('Akhir window pengisian laporan');

            $table->boolean('is_active')->default(true)
                ->comment('Periode aktif untuk pengisian baru');

            $table->timestamps();

            // Setiap kombinasi bulan-tahun harus unik
            $table->unique(['period_month', 'period_year'], 'uq_kpi_period_month_year');
            $table->index(['period_year', 'period_month'], 'idx_kpi_period_year_month');
        });

        // ══════════════════════════════════════════════════════════════════
        // 2. KPI ITEMS
        //    Master data item KPI (lagging & leading).
        //    Tidak berubah per laporan; dikelola via seeder/admin.
        //
        //    Rumus skor:
        //      Lagging all       → SUM (nilai × bobot)
        //      Leading ∑ items   → SUM (item_no: 1,7,8,9,13)
        //      Leading % items   → AVG rata-rata (item_no: 2,3,4,5,6,10,11,12,14,15)
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->enum('section', ['lagging', 'leading'])
                ->comment('Seksi: lagging atau leading indicator');
            $table->unsignedTinyInteger('item_no')
                ->comment('Nomor urut item dalam seksi');

            $table->text('name')
                ->comment('Nama item + target (dipisah newline jika ada target)');
            $table->text('guidance')->nullable()
                ->comment('Panduan pengisian (ditampilkan sebagai tooltip, bukan kolom tabel)');

            $table->string('unit', 10)->nullable()
                ->comment('Satuan: ∑ (jumlah) atau % (persentase)');

            $table->decimal('bobot', 8, 4)->nullable()
                ->comment('Bobot desimal, misal 0.125 = 12.5%. NULL untuk item As-Reported');

            $table->boolean('is_scored')->default(true)
                ->comment('TRUE = item dinilai (punya bobot); FALSE = As-Reported');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE = item dinonaktifkan, tidak muncul di laporan baru');

            $table->timestamps();

            // Kombinasi section + item_no harus unik
            $table->unique(['section', 'item_no'], 'uq_kpi_item_section_no');
            $table->index(['section', 'is_active'], 'idx_kpi_item_section_active');
        });

        // ══════════════════════════════════════════════════════════════════
        // 3. KPI REPORTS
        //    Satu laporan = satu perusahaan × satu periode.
        //    Status lifecycle: draft → submitted → validated / rejected
        //    SoftDeletes: laporan yang dihapus SA masih bisa di-restore.
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('company_id')
                ->comment('FK ke tabel companies');
            $table->uuid('kpi_period_id')
                ->comment('FK ke kpi_periods');

            // Skor total (dihitung ulang setiap kali HSSE mengisi nilai)
            $table->decimal('total_score_lagging', 10, 4)->nullable()
                ->comment('Total skor bagian lagging');
            $table->decimal('total_score_leading', 10, 4)->nullable()
                ->comment('Total skor bagian leading');
            $table->decimal('total_score', 10, 4)->nullable()
                ->comment('Total = lagging + leading');

            $table->enum('status', ['draft', 'submitted', 'validated', 'rejected'])
                ->default('draft')
                ->comment('Status alur kerja laporan');

            // Audit trail siapa yang melakukan aksi
            $table->uuid('created_by')->nullable()
                ->comment('User yang membuat laporan');
            $table->uuid('submitted_by')->nullable()
                ->comment('User yang mensubmit laporan');
            $table->uuid('validated_by')->nullable()
                ->comment('User HSSE yang memvalidasi / menolak');

            $table->timestamp('submitted_at')->nullable()
                ->comment('Waktu submit terakhir');
            $table->timestamp('validated_at')->nullable()
                ->comment('Waktu validasi / penolakan terakhir');

            $table->softDeletes()
                ->comment('Soft delete — laporan dihapus SA tetap bisa dipulihkan');
            $table->timestamps();

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');

            $table->foreign('kpi_period_id')
                ->references('id')->on('kpi_periods')
                ->onDelete('restrict');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('submitted_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('validated_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // ── Indexes ──────────────────────────────────────────────────
            // Satu perusahaan hanya boleh punya 1 laporan per periode (non-deleted)
            $table->unique(
                ['company_id', 'kpi_period_id', 'deleted_at'],
                'uq_kpi_report_company_period'
            );
            $table->index(['company_id', 'status'], 'idx_kpi_report_company_status');
            $table->index(['kpi_period_id', 'status'], 'idx_kpi_report_period_status');
            $table->index('status', 'idx_kpi_report_status');
            $table->index('total_score', 'idx_kpi_report_score');
        });

        // ══════════════════════════════════════════════════════════════════
        // 4. KPI REPORT VESSELS
        //    Data kapal / unit yang dilaporkan dalam satu laporan KPI.
        //    Satu laporan bisa punya banyak kontrak / kapal.
        //    vessel_count = free text (misal: "2 unit", "3 kapal + 1 crew boat")
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_report_vessels', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('kpi_report_id');

            $table->string('vessel_name', 500)
                ->comment('Nama kapal / unit logistik / transport. Boleh multi-nama dalam 1 baris.');
            $table->string('vessel_count', 200)->nullable()
                ->comment('Jumlah kapal — free text, misal: "2 unit", "3 kapal"');
            $table->string('contract_number', 150)->nullable()
                ->comment('Nomor kontrak');
            $table->date('contract_end_date')->nullable()
                ->comment('Tanggal akhir kontrak');
            $table->unsignedSmallInteger('sort_order')->default(0)
                ->comment('Urutan tampil di tabel');

            $table->timestamps();

            $table->foreign('kpi_report_id')
                ->references('id')->on('kpi_reports')
                ->onDelete('cascade');

            $table->index(['kpi_report_id', 'sort_order'], 'idx_krv_report_sort');
        });

        // ══════════════════════════════════════════════════════════════════
        // 5. KPI REPORT DETAILS
        //    Satu baris per item KPI per laporan.
        //    Diisi koordinator (actual_count, keterangan),
        //    dan dinilai HSSE (nilai, score, hsse_catatan).
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_report_details', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('kpi_report_id');
            $table->uuid('kpi_item_id');

            // ── Diisi KOORDINATOR ─────────────────────────────────────────
            $table->double('actual_count', 15, 4)->nullable()
                ->comment('∑ Jumlah atau % aktual implementasi. Wajib diisi sebelum submit.');
            $table->text('keterangan')->nullable()
                ->comment('Keterangan implementasi bulan berjalan');

            // ── Diisi HSSE ────────────────────────────────────────────────
            $table->decimal('nilai', 6, 2)->nullable()
                ->comment('Nilai 0–100 yang diberikan HSSE');
            $table->decimal('score', 10, 4)->nullable()
                ->comment('Score = nilai × bobot (dihitung otomatis)');
            $table->text('hsse_catatan')->nullable()
                ->comment('Catatan verifikasi HSSE per item');

            // ── Status review ─────────────────────────────────────────────
            $table->enum('review_status', ['approved', 'rejected'])->nullable()
                ->comment('Hasil review HSSE untuk item ini: approved atau rejected');

            // ── Flag / Mark HSSE ──────────────────────────────────────────
            $table->boolean('is_marked')->default(false)
                ->comment('HSSE menandai item perlu perhatian khusus');
            $table->string('mark_note', 500)->nullable()
                ->comment('Catatan untuk item yang ditandai');

            $table->timestamps();

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('kpi_report_id')
                ->references('id')->on('kpi_reports')
                ->onDelete('cascade');

            $table->foreign('kpi_item_id')
                ->references('id')->on('kpi_items')
                ->onDelete('restrict');

            // ── Indexes ──────────────────────────────────────────────────
            // Satu detail per item per laporan
            $table->unique(['kpi_report_id', 'kpi_item_id'], 'uq_krd_report_item');
            $table->index('review_status', 'idx_krd_review_status');
            $table->index('is_marked', 'idx_krd_is_marked');
        });

        // ══════════════════════════════════════════════════════════════════
        // 6. KPI REPORT DETAIL EVIDENCES
        //    Lampiran bukti (PDF / foto) per detail item.
        //    Lagging No.1–5 share satu pool lampiran di anchor item (No.1).
        //    Wajib: semua item is_scored=true minimal 1 lampiran sebelum submit.
        //    Max per file: 2 MB. Format: jpg, jpeg, png, webp, pdf.
        //    SoftDeletes: file yang dihapus bisa dilacak untuk cleanup storage.
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_report_detail_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('kpi_report_detail_id')
                ->comment('FK ke kpi_report_details');

            $table->string('file_path', 500)
                ->comment('Path relatif di storage/app/public/');
            $table->string('file_name', 255)
                ->comment('Nama file original saat upload');
            $table->string('file_type', 100)->nullable()
                ->comment('MIME type, misal: image/jpeg, application/pdf');
            $table->unsignedInteger('file_size')->nullable()
                ->comment('Ukuran file dalam bytes. Max: 2097152 (2 MB)');

            $table->string('caption', 500)->nullable()
                ->comment('Keterangan / caption lampiran (opsional)');
            $table->unsignedSmallInteger('sort_order')->default(0)
                ->comment('Urutan tampil thumbnail');

            $table->uuid('uploaded_by')->nullable()
                ->comment('User yang mengupload lampiran');

            $table->softDeletes()
                ->comment('Soft delete — file fisik dihapus via observer/cleanup command');
            $table->timestamps();

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('kpi_report_detail_id')
                ->references('id')->on('kpi_report_details')
                ->onDelete('cascade');

            $table->foreign('uploaded_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['kpi_report_detail_id', 'sort_order'], 'idx_krde_detail_sort');
            $table->index('uploaded_by', 'idx_krde_uploaded_by');
        });

        // ══════════════════════════════════════════════════════════════════
        // 7. KPI REPORT ITEM REVIEWS
        //    Riwayat keputusan review HSSE per item per submission round.
        //    Satu item bisa direview lebih dari sekali (submit ulang setelah reject).
        //    submission_round: berapa kali laporan ini sudah disubmit.
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_report_item_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('kpi_report_detail_id')
                ->comment('Item yang direview');
            $table->uuid('reviewed_by')
                ->comment('User HSSE yang melakukan review');

            $table->enum('decision', ['approved', 'rejected'])
                ->comment('Keputusan review');
            $table->text('comment')->nullable()
                ->comment('Catatan reviewer. Wajib diisi jika decision = rejected.');

            $table->boolean('is_marked')->default(false)
                ->comment('HSSE menandai item perlu perhatian');
            $table->string('mark_note', 500)->nullable()
                ->comment('Catatan khusus item yang ditandai');

            $table->unsignedSmallInteger('submission_round')->default(1)
                ->comment('Ke-berapa kali laporan disubmit saat review ini dilakukan');

            $table->timestamp('reviewed_at')
                ->comment('Waktu review dilakukan');

            $table->timestamps();

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('kpi_report_detail_id')
                ->references('id')->on('kpi_report_details')
                ->onDelete('cascade');

            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['kpi_report_detail_id', 'reviewed_at'], 'idx_krir_detail_reviewed');
            $table->index(['kpi_report_detail_id', 'submission_round'], 'idx_krir_detail_round');
            $table->index('reviewed_by', 'idx_krir_reviewed_by');
        });

        // ══════════════════════════════════════════════════════════════════
        // 8. KPI REPORT STATUS LOGS
        //    Audit trail setiap perubahan status laporan.
        //    Mencatat from_status → to_status, siapa, kapan, dan komentar.
        //    Digunakan untuk menghitung submission_round dan riwayat timeline.
        // ══════════════════════════════════════════════════════════════════
        Schema::create('kpi_report_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('kpi_report_id');

            $table->enum('from_status', ['draft', 'submitted', 'validated', 'rejected'])
                ->nullable()
                ->comment('Status sebelumnya. NULL untuk log pertama (created)');
            $table->enum('to_status', ['draft', 'submitted', 'validated', 'rejected'])
                ->comment('Status sesudahnya');

            $table->text('comment')->nullable()
                ->comment('Catatan / komentar saat transisi status');

            $table->uuid('acted_by')->nullable()
                ->comment('User yang melakukan aksi perubahan status');
            $table->timestamp('acted_at')
                ->comment('Waktu aksi dilakukan');

            $table->timestamps();

            // ── Foreign Keys ─────────────────────────────────────────────
            $table->foreign('kpi_report_id')
                ->references('id')->on('kpi_reports')
                ->onDelete('cascade');

            $table->foreign('acted_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // ── Indexes ──────────────────────────────────────────────────
            $table->index(['kpi_report_id', 'acted_at'], 'idx_krsl_report_acted');
            $table->index(['kpi_report_id', 'to_status'], 'idx_krsl_report_status');
            $table->index('acted_by', 'idx_krsl_acted_by');
        });
    }

    // ════════════════════════════════════════════════════════════════════
    // DOWN — drop semua tabel dalam urutan kebalikan (FK safe)
    // ════════════════════════════════════════════════════════════════════
    public function down(): void
    {
        // Nonaktifkan FK checks sementara agar bisa drop tanpa urutan strict
        Schema::disableForeignKeyConstraints();
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};