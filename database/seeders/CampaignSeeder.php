<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Campaign;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('campaigns');

        $campaigns = [
            [
                'title' => 'Peningkatan Keamanan Operasional di Area Kilang',
                'category' => 'safety',
                'summary' => 'Program audit dan peningkatan standar keselamatan di area kilang untuk mencegah kecelakaan kerja.',
            ],
            [
                'title' => 'Optimasi Produksi Sumur Minyak Menggunakan AI',
                'category' => 'technology',
                'summary' => 'Pemanfaatan AI untuk meningkatkan efisiensi produksi sumur dan memprediksi potensi decline rate.',
            ],
            [
                'title' => 'Proyek Pengembangan Lapangan Gas Baru 2025',
                'category' => 'project',
                'summary' => 'Pengembangan lapangan gas baru untuk meningkatkan kapasitas suplai nasional.',
            ],
            [
                'title' => 'Program Pemeliharaan Pipeline Trans-Regio',
                'category' => 'maintenance',
                'summary' => 'Inspeksi dan pemeliharaan berkala untuk memastikan integritas pipa distribusi migas.',
            ],
            [
                'title' => 'Penerapan Sistem Digital Monitoring Kilang',
                'category' => 'technology',
                'summary' => 'Digitalisasi proses monitoring kilang untuk meningkatkan efisiensi dan respons cepat.',
            ],
            [
                'title' => 'Kampanye Zero Spill Campaign 2025',
                'category' => 'environment',
                'summary' => 'Komitmen perusahaan untuk menekan angka tumpahan minyak hingga 0% melalui prosedur baru.',
            ],
            [
                'title' => 'Training Emergency Response Team (ERT) Migas',
                'category' => 'safety',
                'summary' => 'Pelatihan terpadu untuk meningkatkan respon terhadap insiden darurat migas.',
            ],
            [
                'title' => 'Implementasi Green Refinery Technology',
                'category' => 'environment',
                'summary' => 'Transformasi kilang menuju teknologi yang lebih ramah lingkungan.',
            ],
            [
                'title' => 'Pengembangan Offshore Platform Generasi Baru',
                'category' => 'project',
                'summary' => 'Perencanaan dan pembangunan platform lepas pantai dengan standar internasional.',
            ],
            [
                'title' => 'Digital Twin untuk Pemantauan Lapangan Migas',
                'category' => 'technology',
                'summary' => 'Simulasi digital untuk memonitor operasional lapangan migas secara real-time.',
            ],
        ];

        foreach ($campaigns as $index => $item) {

            $filename = 'campaign_oilgas_' . ($index + 1) . '.jpg';
            $this->generateDummyOilGasImage(storage_path('app/public/campaigns/' . $filename));

            $title = $item['title'];

            Campaign::create([
                'id'            => Str::uuid(),
                'created_by'    => null,
                'title'         => $title,
                'slug'          => Str::slug($title) . '-' . Str::random(5),
                'media'         => 'campaigns/' . $filename,
                'media_type'    => 'image',
                'summary'       => $item['summary'],
                'content'       => "<p>{$item['summary']}</p><p>Informasi lengkap terkait program dan implementasinya.</p>",
                'category'      => $item['category'],
                'published_at'  => now()->subDays(rand(1, 30)),
                'is_published'  => 1,
                'is_featured'   => rand(0, 1),
                'view_count'    => rand(50, 500),
            ]);
        }
    }

    /**
     * Dummy image khusus Oil & Gas
     */
    private function generateDummyOilGasImage($path)
    {
        $img = imagecreatetruecolor(900, 550);

        // Warna industrial (abu, biru gelap, orange safety)
        $colors = [
            [40, 40, 40],     // Dark Industrial Grey
            [25, 45, 75],     // Oil & Gas Navy Blue
            [200, 100, 30],   // Safety Orange
        ];

        $pick = $colors[array_rand($colors)];
        $bg = imagecolorallocate($img, $pick[0], $pick[1], $pick[2]);
        imagefill($img, 0, 0, $bg);

        // Text
        $white = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 20, 20, "Oil & Gas Campaign", $white);

        imagejpeg($img, $path, 90);
        imagedestroy($img);
    }
}
