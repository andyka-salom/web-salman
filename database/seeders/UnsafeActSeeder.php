<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UnsafeAct;

class UnsafeActSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel (aman untuk UUID + soft delete)
        // DB::table('unsafe_acts')->truncate();

        $unsafeActs = [
            'Mengoperasikan peralatan tanpa wewenang',
            'Gagal untuk memberitahu / memperingatkan',
            'Gagal untuk mengamankan',
            'Mengoperasikan alat dengan kecepatan tidak tepat',
            'Membuat peralatan HSSE menjadi tidak berfungsi',
            'Menggunakan alat/peralatan/mesin dalam kondisi rusak',
            'Mengoperasikan alat / peralatan / mesin / perangkat dengan tidak tepat',
            'Servis peralatan / mesin yang sedang beroperasi tidak memadai',
            'Menggunakan bahan / material yang tidak tepat',
            'Gagal menggunakan APD dengan tepat',
            'Pemuatan (loading) yang tidak tepat',
            'Penempatan yang tidak tepat',
            'Pengangkatan yang tidak tepat',
            'Penempatan posisi yang tidak tepat',
            'Perilaku yang tidak tepat / tidak pantas',
            'Dibawah pengaruh obat / narkoba / alkohol',
            'Gagal mengikuti prosedur / instruksi',
            'Gagal mengidentifikasi bahaya',
            'Tindakan tidak aman oleh pihak eksternal (di luar kendali perusahaan)',
        ];

        foreach ($unsafeActs as $index => $description) {
            UnsafeAct::create([
                'id'           => Str::uuid(),
                'code'         => 'UA-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'description'  => $description,
                'is_active'    => true,
                'usage_count'  => 0,
            ]);
        }
    }
}
