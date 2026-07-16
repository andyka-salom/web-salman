<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name'    => 'PHM (Pertamina Hulu Mahakam)',
                'slug'    => Str::slug('PHM Pertamina Hulu Mahakam'),
                'address' => 'Jl. Yos Sudarso No. 1, Balikpapan, Kalimantan Timur',
            ],
            [
                'name'    => 'Logindo',
                'slug'    => Str::slug('Logindo'),
                'address' => 'Jl. Raya Pelabuhan No. 88, Jakarta Utara',
            ],
            [
                'name'    => 'Samudra Marine Service',
                'slug'    => Str::slug('Samudra Marine Service'),
                'address' => 'Jl. Samudera No. 50, Surabaya',
            ],
        ];

        foreach ($companies as $data) {
            Company::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'id'        => Str::uuid(),
                    'name'      => $data['name'],
                    'slug'      => $data['slug'],
                    'address'   => $data['address'],
                    'is_active' => true,
                ]
            );
        }
    }
}
