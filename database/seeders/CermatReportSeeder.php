<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CermatReport;
use App\Models\User;
use App\Models\Area;
use App\Models\Pelanggaran;
use App\Models\UnsafeAct;
use App\Models\UnsafeCondition;
use App\Models\ActionItem;
use App\Models\ActionCategory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CermatReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil crew kapal
        $crewSmallMarine = User::where('email', 'crew.smallmarine@pertamina.com')->first();
        $crewBigMarine = User::where('email', 'crew.bigmarine@pertamina.com')->first();

        // Ambil supervisor untuk approval
        $supervisorSmallMarine = User::where('email', 'phm.sup.swa.smallmarine.supv.hca@pertamina.com')->first();
        $supervisorBigMarine = User::where('email', 'phm.sup.swa.bigmarine.supv@pertamina.com')->first();

        // Ambil HSSE Officer
        $hsseOfficer = User::where('email', 'phm.hsse.wilma.officer@pertamina.com')->first();

        if (!$crewSmallMarine || !$crewBigMarine || !$supervisorSmallMarine || !$supervisorBigMarine || !$hsseOfficer) {
            $this->command->error('Users tidak ditemukan. Pastikan UserSeeder sudah dijalankan.');
            return;
        }

        // Ambil data master
        $areas = Area::all();
        $pelanggarans = Pelanggaran::all();
        $unsafeActs = UnsafeAct::all();
        $unsafeConditions = UnsafeCondition::all();
        $actionCategories = ActionCategory::all();

        if ($areas->isEmpty() || $pelanggarans->isEmpty()) {
            $this->command->error('Master data tidak lengkap. Jalankan seeder Area dan Pelanggaran terlebih dahulu.');
            return;
        }

        // Counter untuk report number
        $reportCounter = 1;

        // Template data laporan untuk variasi
        $reportTemplates = [
            [
                'classification' => 'near_miss',
                'event_type' => 'hse',
                'details' => 'Pekerja melakukan pekerjaan panas tanpa hot work permit yang valid.',
                'location_details' => 'Deck Area, Bagian Belakang Kapal',
                'immediate_action_taken' => 'Pekerjaan dihentikan segera dan dilakukan briefing keselamatan.',
                'suggestion_for_improvement' => 'Perlu penguatan sistem permit to work dan monitoring yang lebih ketat.',
                'value_of_loss' => 500000,
            ],
            [
                'classification' => 'anomaly',
                'event_type' => 'hse',
                'details' => 'Ditemukan kondisi lantai licin akibat tumpahan oli di area kerja.',
                'location_details' => 'Engine Room, Level 2',
                'immediate_action_taken' => 'Area dibersihkan dan dipasang tanda peringatan.',
                'suggestion_for_improvement' => 'Implementasi housekeeping schedule yang lebih teratur.',
                'value_of_loss' => 250000,
            ],
            [
                'classification' => 'incident',
                'event_type' => 'hse',
                'details' => 'Pekerja tidak menggunakan safety harness saat bekerja di ketinggian.',
                'location_details' => 'Mast Area, 5 meter dari deck',
                'immediate_action_taken' => 'Pekerja diturunkan dan diberikan toolbox talk tentang work at height.',
                'suggestion_for_improvement' => 'Perlu pelatihan ulang tentang working at height dan pengawasan lebih ketat.',
                'value_of_loss' => 1000000,
            ],
            [
                'classification' => 'anomaly',
                'event_type' => 'hse',
                'details' => 'Fire extinguisher dalam kondisi rusak dan tidak layak pakai.',
                'location_details' => 'Galley Area',
                'immediate_action_taken' => 'Fire extinguisher diganti dengan yang baru dan dilakukan inspeksi menyeluruh.',
                'suggestion_for_improvement' => 'Jadwal inspeksi alat pemadam kebakaran harus lebih rutin.',
                'value_of_loss' => 150000,
            ],
            [
                'classification' => 'positive_aspect',
                'event_type' => 'hse',
                'details' => 'Crew melakukan identifikasi bahaya dengan baik dan melaporkan potensi risiko sebelum terjadi kecelakaan.',
                'location_details' => 'Cargo Deck',
                'immediate_action_taken' => 'Dilakukan perbaikan segera dan apresiasi diberikan kepada pelapor.',
                'suggestion_for_improvement' => 'Sistem pelaporan proaktif perlu dipertahankan dan ditingkatkan.',
                'value_of_loss' => 0,
            ],
        ];

        // Status templates untuk variasi
        $statusTemplates = [
            // Draft - Belum submit
            [
                'status' => CermatReport::STATUS_DRAFT,
                'supervisor_status' => CermatReport::SUPERVISOR_STATUS_NOT_REQUIRED,
                'hsse_status' => CermatReport::HSSE_STATUS_PENDING_REVIEW,
                'submitted_at' => null,
            ],
            // In Review - Menunggu approval supervisor
            [
                'status' => CermatReport::STATUS_IN_REVIEW,
                'supervisor_status' => CermatReport::SUPERVISOR_STATUS_AWAITING,
                'hsse_status' => CermatReport::HSSE_STATUS_PENDING_REVIEW,
                'submitted_at' => now()->subDays(2),
            ],
            // Approved & Reviewed - Sedang action
            [
                'status' => CermatReport::STATUS_ACTION_IN_PROGRESS,
                'supervisor_status' => CermatReport::SUPERVISOR_STATUS_APPROVED,
                'hsse_status' => CermatReport::HSSE_STATUS_REVIEWED,
                'submitted_at' => now()->subDays(5),
                'supervisor_approved_at' => now()->subDays(4),
                'hsse_reviewed_at' => now()->subDays(3),
            ],
            // Rejected - Perlu perbaikan
            [
                'status' => CermatReport::STATUS_IN_REVIEW,
                'supervisor_status' => CermatReport::SUPERVISOR_STATUS_REJECTED,
                'hsse_status' => CermatReport::HSSE_STATUS_PENDING_REVIEW,
                'submitted_at' => now()->subDays(3),
                'supervisor_rejected_at' => now()->subDays(2),
                'rejection_reason' => 'Informasi tidak lengkap. Harap tambahkan detail lokasi dan saksi.',
            ],
            // Closed - Selesai
            [
                'status' => CermatReport::STATUS_CLOSED,
                'supervisor_status' => CermatReport::SUPERVISOR_STATUS_APPROVED,
                'hsse_status' => CermatReport::HSSE_STATUS_REVIEWED,
                'submitted_at' => now()->subDays(15),
                'supervisor_approved_at' => now()->subDays(14),
                'hsse_reviewed_at' => now()->subDays(13),
                'close_out_at' => now()->subDays(1),
                'close_out_description' => 'Semua action item telah diselesaikan dengan baik.',
            ],
        ];

        // Generate 5 reports untuk Crew Small Marine
        foreach ($reportTemplates as $index => $template) {
            $statusTemplate = $statusTemplates[$index];

            $report = CermatReport::create([
                'id' => Str::uuid(),
                'report_number' => 'CERMAT-SM-' . now()->format('Ymd') . '-' . str_pad($reportCounter++, 4, '0', STR_PAD_LEFT),
                'reporter_id' => $crewSmallMarine->id,
                'line_supervisor_id' => $supervisorSmallMarine->id,
                'hsse_officer_id' => $hsseOfficer->id,
                'area_id' => $areas->random()->id,
                'pelanggaran_id' => $pelanggarans->random()->id,

                'classification' => $template['classification'],
                'event_type' => $template['event_type'],
                'details' => $template['details'],
                'location_details' => $template['location_details'],
                'immediate_action_taken' => $template['immediate_action_taken'],
                'suggestion_for_improvement' => $template['suggestion_for_improvement'],

                'report_datetime' => now()->subDays(rand(1, 7)),
                'internal_deadline' => now()->addDays(rand(7, 30)),

                'status' => $statusTemplate['status'],
                'supervisor_status' => $statusTemplate['supervisor_status'],
                'hsse_status' => $statusTemplate['hsse_status'],

                'submitted_at' => $statusTemplate['submitted_at'],
                'supervisor_approved_at' => $statusTemplate['supervisor_approved_at'] ?? null,
                'supervisor_rejected_at' => $statusTemplate['supervisor_rejected_at'] ?? null,
                'hsse_reviewed_at' => $statusTemplate['hsse_reviewed_at'] ?? null,
                'close_out_at' => $statusTemplate['close_out_at'] ?? null,

                'rejection_reason' => $statusTemplate['rejection_reason'] ?? null,
                'close_out_description' => $statusTemplate['close_out_description'] ?? null,
                'supervisor_notes' => $statusTemplate['supervisor_status'] === CermatReport::SUPERVISOR_STATUS_APPROVED
                    ? 'Laporan sudah sesuai dan dapat dilanjutkan ke tahap berikutnya.'
                    : null,
            ]);

            // Attach unsafe acts/conditions
            if ($template['classification'] === 'unsafe_act') {
                $report->unsafeActs()->attach($unsafeActs->random(rand(1, 3))->pluck('id'));
            } else {
                $report->unsafeConditions()->attach($unsafeConditions->random(rand(1, 3))->pluck('id'));
            }

            // Create action items untuk laporan yang sudah approved
            if (in_array($report->status, [CermatReport::STATUS_ACTION_IN_PROGRESS, CermatReport::STATUS_CLOSED])) {
                $this->createActionItems($report, $supervisorSmallMarine, $actionCategories);
            }
        }

        // Generate 5 reports untuk Crew Big Marine
        foreach ($reportTemplates as $index => $template) {
            $statusTemplate = $statusTemplates[$index];

            $report = CermatReport::create([
                'id' => Str::uuid(),
                'report_number' => 'CERMAT-BM-' . now()->format('Ymd') . '-' . str_pad($reportCounter++, 4, '0', STR_PAD_LEFT),
                'reporter_id' => $crewBigMarine->id,
                'line_supervisor_id' => $supervisorBigMarine->id,
                'hsse_officer_id' => $hsseOfficer->id,
                'area_id' => $areas->random()->id,
                'pelanggaran_id' => $pelanggarans->random()->id,

                'classification' => $template['classification'],
                'event_type' => $template['event_type'],
                'details' => $template['details'],
                'location_details' => $template['location_details'],
                'immediate_action_taken' => $template['immediate_action_taken'],
                'suggestion_for_improvement' => $template['suggestion_for_improvement'],

                'report_datetime' => now()->subDays(rand(1, 7)),
                'internal_deadline' => now()->addDays(rand(7, 30)),

                'status' => $statusTemplate['status'],
                'supervisor_status' => $statusTemplate['supervisor_status'],
                'hsse_status' => $statusTemplate['hsse_status'],

                'submitted_at' => $statusTemplate['submitted_at'],
                'supervisor_approved_at' => $statusTemplate['supervisor_approved_at'] ?? null,
                'supervisor_rejected_at' => $statusTemplate['supervisor_rejected_at'] ?? null,
                'hsse_reviewed_at' => $statusTemplate['hsse_reviewed_at'] ?? null,
                'close_out_at' => $statusTemplate['close_out_at'] ?? null,

                'rejection_reason' => $statusTemplate['rejection_reason'] ?? null,
                'close_out_description' => $statusTemplate['close_out_description'] ?? null,
                'supervisor_notes' => $statusTemplate['supervisor_status'] === CermatReport::SUPERVISOR_STATUS_APPROVED
                    ? 'Laporan sudah sesuai dan dapat dilanjutkan ke tahap berikutnya.'
                    : null,
            ]);

            // Attach unsafe acts/conditions
            if ($template['classification'] === 'unsafe_act') {
                $report->unsafeActs()->attach($unsafeActs->random(rand(1, 3))->pluck('id'));
            } else {
                $report->unsafeConditions()->attach($unsafeConditions->random(rand(1, 3))->pluck('id'));
            }

            // Create action items untuk laporan yang sudah approved
            if (in_array($report->status, [CermatReport::STATUS_ACTION_IN_PROGRESS, CermatReport::STATUS_CLOSED])) {
                $this->createActionItems($report, $supervisorBigMarine, $actionCategories);
            }
        }

        $this->command->info('✅ Berhasil membuat 10 laporan CERMAT (5 untuk setiap crew).');
    }

    /**
     * Helper untuk membuat action items
     */
    private function createActionItems($report, $responsible, $actionCategories)
    {
        $actionCount = rand(2, 4);
        $statuses = [
            ActionItem::STATUS_DO,
            ActionItem::STATUS_IN_PROGRESS,
            ActionItem::STATUS_COMPLETED,
        ];

        for ($i = 0; $i < $actionCount; $i++) {
            $category = $actionCategories->random();
            $status = $statuses[array_rand($statuses)];

            // Tentukan target date berdasarkan kategori
            $targetDate = match($category->duration_range) {
                '<7' => now()->addDays(rand(1, 6)),
                '8-15' => now()->addDays(rand(8, 15)),
                '16-30' => now()->addDays(rand(16, 30)),
                '>30' => now()->addDays(rand(31, 60)),
                default => now()->addDays(14),
            };

            ActionItem::create([
                'id' => Str::uuid(),
                'cermat_report_id' => $report->id,
                'responsible_id' => $responsible->id,
                'action_category_id' => $category->id,
                'description' => 'Tindakan korektif #' . ($i + 1) . ': ' . $this->getActionDescription($i),
                'target_date' => $targetDate,
                'current_target_date' => $targetDate,
                'status' => $status,
                'extension_count' => 0,
                'is_overdue' => false,
                'completed_at' => $status === ActionItem::STATUS_COMPLETED ? now()->subDays(rand(1, 3)) : null,
                'completion_notes' => $status === ActionItem::STATUS_COMPLETED
                    ? 'Tindakan telah diselesaikan sesuai dengan standar keselamatan.'
                    : null,
            ]);
        }
    }

    /**
     * Helper untuk deskripsi action item
     */
    private function getActionDescription($index): string
    {
        $descriptions = [
            'Perbaikan peralatan safety yang rusak',
            'Pelatihan ulang untuk crew terkait prosedur keselamatan',
            'Inspeksi menyeluruh area kerja',
            'Update risk assessment',
            'Implementasi sistem monitoring baru',
            'Pengadaan APD tambahan',
            'Perbaikan kondisi lingkungan kerja',
        ];

        return $descriptions[$index % count($descriptions)];
    }
}
