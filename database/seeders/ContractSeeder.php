<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contracts')->insert([
            [
                'nama_kontraktor' => 'PT Maju Jaya Abadi',
                'sap_no' => 'SAP-001',
                'alamat_kantor' => 'Jl. Mawar No.10, Jakarta Selatan',
                'alamat_email' => 'contact@majujaya.com',
                'no_tlp_kantor' => '021-1234567',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kontraktor' => 'PT Cipta Karya Mandiri',
                'sap_no' => 'SAP-002',
                'alamat_kantor' => 'Jl. Melati No.5, Bandung',
                'alamat_email' => 'info@cipta-karya.com',
                'no_tlp_kantor' => '022-7654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kontraktor' => 'PT Sukses Bersama',
                'sap_no' => 'SAP-003',
                'alamat_kantor' => 'Jl. Kenanga No.22, Surabaya',
                'alamat_email' => 'admin@suksesbersama.co.id',
                'no_tlp_kantor' => '031-9988776',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
