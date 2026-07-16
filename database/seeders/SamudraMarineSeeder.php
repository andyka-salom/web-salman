<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\Vessel;
use App\Models\CrewMember;
use App\Models\User;

class SamudraMarineSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', 'samudra-marine-service')->firstOrFail();
        $coordinator = User::first();

        $vesselNames = [
            "SMS Voyager",
            "SMS Mariner",
            "SMS Oceanic",
            "SMS Horizon",
            "SMS Neptune"
        ];

        foreach ($vesselNames as $index => $vName) {
            $vessel = Vessel::create([
                'id'            => Str::uuid(),
                'company_id'    => $company->id,
                'coordinator_id'=> $coordinator->id,
                'name'          => $vName,
                'code'          => 'SMS-' . ($index + 1),
                'type'          => 'Crew Boat',
                'valid_until'   => now()->addYears(5),
                'is_active'     => true,
            ]);

            // 10 Crew per vessel
            for ($i = 1; $i <= 10; $i++) {
                CrewMember::create([
                    'id'          => Str::uuid(),
                    'vessel_id'   => $vessel->id,
                    'created_by'  => $coordinator->id,
                    'name'        => "Crew {$vName} {$i}",
                    'nik'         => strtoupper(Str::random(12)),
                    'position'    => $i == 1 ? 'Captain' : 'Crew',
                    'gender'      => 'male',
                    'birth_date'  => now()->subYears(rand(22, 45)),
                    'age'         => rand(22, 45),
                    'blood_type'  => 'A',
                    'phone'       => '08' . rand(1111111111, 9999999999),
                    'address'     => 'Indonesia',
                    'is_active'   => true,
                ]);
            }
        }
    }
}
