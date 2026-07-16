<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Pelanggaran;

class PelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Tools & Equipment',
            'Line of Fire',
            'Hot Work',
            'Confined Space',
            'Powered System',
            'Lifting Operation',
            'Working at Height',
            'Ground Disturbance',
            'Water Based Activities',
            'Land Transportation',
        ];

        foreach ($data as $index => $name) {
            Pelanggaran::create([
                'id'              => Str::uuid(),
                // PERUBAHAN: Menghapus 'code'
                'sequence_number' => $index + 1, // Menggunakan index + 1 sebagai nomer urut
                'name'            => $name,
                'description'     => null,
                'severity'        => 'medium',   // default severity (bisa dibuat per item)
                'is_active'       => true,
            ]);
        }
    }
}
