<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
{
    public function run()
    {
        $areas = [
            'SPS',
            'HCA',
            'CPU',
            'NPU',
            'SPU',
            'Sisi Nubi',
            'Bekapai',
        ];

        foreach ($areas as $index => $name) {
            Area::create([
                'id'         => Str::uuid(),
                'code'       => 'AREA-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT), // kode otomatis
                'name'       => $name,
                'company_id' => null, // biarkan null karena nullable
                'is_active'  => true,
            ]);
        }

        $this->command->info('Tabel Areas berhasil diisi dengan data awal.');
    }
}
