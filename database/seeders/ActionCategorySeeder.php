<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ActionCategory;

class ActionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catatan: Jika Anda menggunakan ActionCategory::create(), Anda tidak perlu
        // secara manual memanggil DB::table()->truncate() kecuali Anda ingin menghapus
        // semua data tanpa memicu event model (seperti yang dilakukan SoftDeletes).

        $items = [
            [
                'name'          => 'Immediate Action (< 7 Days)',
                'duration_range'=> '<7',
                'priority'      => 1,
            ],
            [
                'name'          => 'Short Term Action (8-15 Days)',
                'duration_range'=> '8-15',
                'priority'      => 2,
            ],
            [
                'name'          => 'Medium Term Action (16-30 Days)',
                'duration_range'=> '16-30',
                'priority'      => 3,
            ],
            [
                'name'          => 'Long Term Action (> 30 Days)',
                'duration_range'=> '>30',
                'priority'      => 4,
            ],
        ];

        foreach ($items as $index => $item) {
            ActionCategory::create([
                'id'            => Str::uuid(),
                // Membuat kode aksi yang sesuai
                'code'          => 'ACT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'name'          => $item['name'],

                // Perubahan: Menggunakan duration_range
                'duration_range'=> $item['duration_range'],

                // Perubahan: Menghapus kolom 'color'

                'priority'      => $item['priority'],
                'is_active'     => true,
            ]);
        }
    }
}
