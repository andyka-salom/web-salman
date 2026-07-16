<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecurityEventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'Perusakan Aset PHM',
            'Serangan Verbal terhadap Karyawan PHM',
            'Serangan Fisik terhadap Karyawan PHM',
            'Pencurian Aset PHM tanpa Kekerasan',
            'Pencurian Aset PHM dengan Kekerasan',
            'Penculikan',
            'Penyusupan ke PHM Site',
            'Blokade PHM Site',
            'Kerusuhan Eksternal',
            'Kecurangan',
            'Penyerangan Sistem Informasi',
            'Serangan Media',
            'Kategori Lain',
        ];

        foreach ($items as $index => $name) {
            DB::table('security_event_categories')->insert([
                'id'          => Str::uuid(),
                'code'        => 'SEC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'name'        => $name,
                'description' => null,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
