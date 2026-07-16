<?php

namespace Database\Seeders;

use App\Models\KpiItem;
use Illuminate\Database\Seeder;

/**
 * KpiItemSeeder — Production v3
 * Sesuai KPI HSSE Kontraktor Rev01 (002) Excel.
 *
 * Rumus:
 *   SUM  (∑): Lagging semua + Leading No. 1, 7, 8, 9, 13
 *   AVG  (%): Leading No. 2, 3, 4, 5, 6, 10, 11, 12, 14, 15
 */
class KpiItemSeeder extends Seeder
{
    public function run(): void
    {
        KpiItem::query()->update(['is_active' => false]); // soft-deactivate lama

        // ── LAGGING INDICATOR ─────────────────────────────────────────────
        // Bobot total lagging: 40% (0.4)
        // Scored items: No. 1–5 (shared 1 lampiran)
        // As-Reported: No. 6 (FAC), 7 (Nearmiss), 8 (Manhours)
        $lagging = [
            [
                'item_no' => 1,
                'name' => "Fatality\n- Target 0",
                'guidance' => 'Jumlah Fatality (NOA) pada bulan berjalan',
                'unit' => '∑',
                'bobot' => 0.125,
                'is_scored' => true,
            ],
            [
                'item_no' => 2,
                'name' => "Lost Time Injury (LTI)\n- Target 0",
                'guidance' => 'Jumlah LTI pada bulan berjalan',
                'unit' => '∑',
                'bobot' => 0.075,
                'is_scored' => true,
            ],
            [
                'item_no' => 3,
                'name' => "Restricted Work Day Case (RWDC)\n- Target 0",
                'guidance' => 'Jumlah RWDC pada bulan berjalan',
                'unit' => '∑',
                'bobot' => 0.075,
                'is_scored' => true,
            ],
            [
                'item_no' => 4,
                'name' => "Medical Treatment Case (MTC)\n- Target 0",
                'guidance' => 'Jumlah MTC pada bulan berjalan',
                'unit' => '∑',
                'bobot' => 0.075,
                'is_scored' => true,
            ],
            [
                'item_no' => 5,
                'name' => "HIPO (High Potential Incident)\n- Target 0",
                'guidance' => 'Jumlah HIPO pada bulan berjalan',
                'unit' => '∑',
                'bobot' => 0.05,
                'is_scored' => true,
            ],
            [
                'item_no' => 6,
                'name' => "First Aid Case (FAC)\n- As reported",
                'guidance' => 'Jumlah FAC pada bulan berjalan',
                'unit' => '∑',
                'bobot' => null,
                'is_scored' => false,
            ],
            [
                'item_no' => 7,
                'name' => "Nearmiss & Incident (No injury, Non HIPO)\n- As reported",
                'guidance' => 'Jumlah nearmiss dan insiden tanpa personal injury - Non HIPO',
                'unit' => '∑',
                'bobot' => null,
                'is_scored' => false,
            ],
            [
                'item_no' => 8,
                'name' => "Manhours\n- As reported",
                'guidance' => 'Jumlah Manhours pada bulan berjalan',
                'unit' => '∑',
                'bobot' => null,
                'is_scored' => false,
            ],
        ];

        foreach ($lagging as $data) {
            KpiItem::updateOrCreate(
                ['section' => 'lagging', 'item_no' => $data['item_no']],
                array_merge($data, ['section' => 'lagging', 'is_active' => true])
            );
        }

        // ── LEADING INDICATOR ─────────────────────────────────────────────
        // Rumus SUM  (∑): No. 1, 7, 8, 9, 13
        // Rumus AVG  (%): No. 2, 3, 4, 5, 6, 10, 11, 12, 14, 15
        // Bobot total leading: 60% (0.6)
        $leading = [
            [
                'item_no' => 1,
                'name' => "Observasi Anomaly (CerMAT, Teman, SWA)\nTarget:\n- Marine: 1 anomaly / kapal or unit / bulan\n- Lifting: 5 anomaly/site/bulan\n- Land transport & other log: 2 anomaly / bulan",
                'guidance' => 'Jumlah CERMAT sesuai dashboard Kapten Salman pada bulan berjalan',
                'unit' => '∑', // SUM
                'bobot' => 0.05,
                'is_scored' => true,
            ],
            [
                'item_no' => 2,
                'name' => "Medical Check Up (MCU)\n- Target kepatuhan: 100%",
                'guidance' => 'Persentasi perbandingan valid MCU vs semua personnel on board / site pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.05,
                'is_scored' => true,
            ],
            [
                'item_no' => 3,
                'name' => "Daily Check Up (DCU)\n- Target kepatuhan: 100%",
                'guidance' => 'Persentasi kepatuhan DCU sesuai dashboard aplikasi Kapten Salman pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.05,
                'is_scored' => true,
            ],
            [
                'item_no' => 4,
                'name' => "Wellness Program (olahraga, menu sehat)\nTarget:\n- Big marine: 1x per bulan / kapal\n- Small marine: 1x per bulan / site\n- Logistik & transport: 1x per bulan / site",
                'guidance' => 'Persentasi realisasi wellness vs jadwal pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.03,
                'is_scored' => true,
            ],
            [
                'item_no' => 5,
                'name' => "Random Test Drug and Alcohol\nTarget:\n- Big marine: 1x per tahun / kapal\n- Small marine / crew boat: 1x per tahun / site\n- Logistik & transport: 1x / tahun",
                'guidance' => 'Persentasi pelaksanaan random test vs rencana pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.03,
                'is_scored' => true,
            ],
            [
                'item_no' => 6,
                'name' => "Training Kompetensi & HSSE\nTarget kepatuhan 100% sesuai training matrix:\n- Small Marine: BFF, BFA, SS, CLSR\n- Big Marine: BFF, BFA, SS, CLSR, SCRB, AFF, Rigger\n- Logistik & transport: BFF, BFA, CSLR, DDT\n- Lifting: BFF, BFA, CSLR, Operator/Rigger",
                'guidance' => 'Persentasi jumlah valid training vs target training pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.03,
                'is_scored' => true,
            ],
            [
                'item_no' => 7,
                'name' => "Kampanye & Rapat HSSE\nTarget:\n- Big marine: 2x per bulan / kapal\n- Small marine / crew boat: 2x per bulan / site\n- Logistik / transport: 2x per bulan\n- Rapat tinjauan manajemen: 2x / tahun",
                'guidance' => 'Jumlah safety campaign / rapat sesuai dashboard KaptenSalman pada bulan berjalan',
                'unit' => '∑', // SUM
                'bobot' => 0.04,
                'is_scored' => true,
            ],
            [
                'item_no' => 8,
                'name' => "Latihan Tanggap Darurat\nTarget:\n- Big marine: 1x per bulan / kapal\n- Small marine / crew boat: 1x per bulan / site\n- Logistik / transport: 1x / tahun\n- Exercise ship-shore (site-office) skenario medevac: 1x / tahun",
                'guidance' => 'Jumlah implementasi drill / exercise pada bulan berjalan',
                'unit' => '∑', // SUM
                'bobot' => 0.04,
                'is_scored' => true,
            ],
            [
                'item_no' => 9,
                'name' => "Inspeksi HSSE (APD, APAR, dll)\nTarget:\n- Big marine: 1x per bulan / kapal\n- Small marine / crew boat: 1x per bulan / site\n- Logistik / transport: 1x / bulan",
                'guidance' => 'Jumlah inspeksi HSSE pada bulan berjalan',
                'unit' => '∑', // SUM
                'bobot' => 0.04,
                'is_scored' => true,
            ],
            [
                'item_no' => 10,
                'name' => "Internal Audit\nTarget: Implementasi sesuai jadwal internal audit (min. 1x/tahun)",
                'guidance' => 'Presentasi pelaksanaan internal audit vs rencana pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.04,
                'is_scored' => true,
            ],
            [
                'item_no' => 11,
                'name' => "Management Walkthrough (MWT)\nTarget:\n- Kolaborasi MWT dengan PHM: 1x / tahun\n- MWT mandiri: 1x / tahun (Top Management / Senior Manager)",
                'guidance' => 'Persentasi implementasi MWT vs rencana pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.04,
                'is_scored' => true,
            ],
            [
                'item_no' => 12,
                'name' => "Penghargaan HSSE\nTarget: Minimum 4x per tahun (quarterly)\n- Best CerMAT / TEMAN / Stop Job\n- Best safety campaign",
                'guidance' => 'Persentasi pemberian apresiasi / penghargaan yang diberikan pada tiap kuartal (3 monthly)',
                'unit' => '%', // AVG — revisi dari '∑' di versi sebelumnya
                'bobot' => 0.03,
                'is_scored' => true,
            ],
            [
                'item_no' => 13,
                'name' => "Pemeriksaan Integrity Kapal / Unit\nTarget 1x / bulan / kapal or unit:\n- Big marine: refer to BM spot inspection\n- Small marine: refer to monthly boat checking\n- Crew boat: refer to integrity check\n- Logistik & transport: refer to monthly inspection",
                'guidance' => 'Jumlah inspeksi dalam bulan berjalan',
                'unit' => '∑', // SUM — revisi dari '%' di versi sebelumnya
                'bobot' => 0.05,
                'is_scored' => true,
            ],
            [
                'item_no' => 14,
                'name' => "Perawatan (Maintenance) Kapal / Unit Logistik / Transportasi\nTarget: Implementasi sesuai jadwal PMS",
                'guidance' => 'Persentasi realisasi perawatan (PMS) vs rencana pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.05,
                'is_scored' => true,
            ],
            [
                'item_no' => 15,
                'name' => "Pemantauan Tindak Lanjut\nTarget: Status pemantauan semua rekomendasi (> 80%)",
                'guidance' => 'Presentasi pencapaian tindak lanjut rekomendasi pada bulan berjalan',
                'unit' => '%', // AVG
                'bobot' => 0.03,
                'is_scored' => true,
            ],
        ];

        foreach ($leading as $data) {
            KpiItem::updateOrCreate(
                ['section' => 'leading', 'item_no' => $data['item_no']],
                array_merge($data, ['section' => 'leading', 'is_active' => true])
            );
        }

        $this->command->info('KpiItem seeder selesai: 8 Lagging + 15 Leading items.');
        $this->command->info('SUM items: Lagging all + Leading No.1,7,8,9,13');
        $this->command->info('AVG items: Leading No.2,3,4,5,6,10,11,12,14,15');
    }
}