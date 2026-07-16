<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UnsafeCondition;

class UnsafeConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel dengan aman untuk UUID + soft delete
        // DB::table('unsafe_conditions')->truncate();

        $unsafeConditions = [
            'Kondisi lantai / permukaan tidak memadai',
            'Alat / peralatan dalam kondisi rusak',
            'Alat / peralatan salah / tidak memadai',
            'Kehandalan (integrity) peralatan tidak memadai',
            'Model / sistem operasi tidak memadai',
            'Kegagalan dalam mendeteksi / mengukur',
            'Pengukuran / pembacaan sinyal tidak tepat',
            'Bahan / material tidak tepat',
            'Komposisi bahan tidak tepat',
            'Pelindung / pembatas tidak memadai',
            'Kondisi APD tidak memadai / tidak layak',
            'Kepadatan / keterbatasan ruang untuk bekerja',
            'Sistem peringatan tidak memadai',
            'Kehadiran atmosfer mudah terbakar / meledak',
            'Kehadiran bahan / material berbahaya yang tidak dikehendaki',
            'Kebersihan / kerapihan buruk',
            'Tingkat kebisingan di atas ambang batas',
            'Bahaya radiasi di atas ambang batas',
            'Kekurangan / kelebihan pencahayaan',
            'Bahaya getaran di atas ambang batas',
            'Paparan terhadap suhu tinggi',
            'Paparan terhadap suhu rendah',
            'Tekanan di luar batasan',
            'Ventilasi tidak memadai',
            'Informasi tidak memadai',
            'Paparan kondisi cuaca yang buruk',
            'Bahaya lingkungan / kondisi kerja lainnya',
            'Bahaya dari sumber eksternal',
        ];

        foreach ($unsafeConditions as $index => $description) {
            UnsafeCondition::create([
                'id'           => Str::uuid(),
                'code'         => 'UC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'description'  => $description,
                'is_active'    => true,
                'usage_count'  => 0,
            ]);
        }
    }
}
