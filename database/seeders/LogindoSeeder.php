<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\Vessel;
use App\Models\CrewMember;
use App\Models\User;
use App\Models\HealthCheck;
use App\Models\EntityFunction; // Added import
use Carbon\Carbon;

class LogindoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Company Exists
        $company = Company::where('slug', 'logindo')->first();

        // If company doesn't exist, create it dynamically to prevent crash
        if (!$company) {
            $company = Company::create([
                'id' => Str::uuid(),
                'name' => 'Logindo',
                'slug' => 'logindo',
                'address' => 'Jakarta',
                'email' => 'logindo@test.com',
                'is_active' => true
            ]);
        }

        // 2. SAFETY FIX: Ensure Coordinator Exists
        // Try to find a user with role 'koordinator', or just get the first user
        $coordinator = User::role('koordinator')->first() ?? User::first();

        // If ABSOLUTELY NO USER exists, create a dummy one
        if (!$coordinator) {
            // We need a dummy entity function id for the user
            $entity = EntityFunction::firstOrCreate(
                ['name' => 'System Fallback'],
                ['code' => 'SYS']
            );

            $coordinator = User::create([
                'name' => 'System Coordinator',
                'email' => 'coordinator@system.com',
                'password' => bcrypt('password'),
                'phone' => '0800000000',
                'jabatan' => 'System Coordinator',
                'entity_function_id' => $entity->id,
                'is_active' => true,
            ]);

            // Assign role if Spatie is installed
            if (method_exists($coordinator, 'assignRole')) {
                // Ensure role exists first
                try {
                    $coordinator->assignRole('koordinator');
                } catch (\Exception $e) {
                    // Ignore if role doesn't exist
                }
            }
        }

        $vesselNames = [
            "Logindo Alpha",
            "Logindo Bravo",
            "Logindo Charlie",
            "Logindo Delta",
            "Logindo Echo"
        ];

        foreach ($vesselNames as $index => $vName) {
            $vessel = Vessel::create([
                'id'            => Str::uuid(),
                'company_id'    => $company->id,
                'coordinator_id'=> $coordinator->id, // Now this is guaranteed not null
                'name'          => $vName,
                'code'          => 'LG-' . ($index + 1),
                'type'          => 'Support Vessel',
                'valid_until'   => now()->addYears(3),
                'is_active'     => true,
            ]);

            // Seeder 10 Crew each Vessel
            for ($i = 1; $i <= 10; $i++) {
                $crew = CrewMember::create([
                    'id'          => Str::uuid(),
                    'vessel_id'   => $vessel->id,
                    'created_by'  => $coordinator->id,
                    'name'        => "Crew {$vName} {$i}",
                    'nik'         => strtoupper(Str::random(12)),
                    'position'    => $i == 1 ? 'Captain' : 'Crew',
                    'gender'      => 'male',
                    'birth_date'  => now()->subYears(rand(22, 45))->subDays(rand(1, 365)),
                    'age'         => rand(22, 45),
                    'blood_type'  => 'O',
                    'phone'       => '08' . rand(1111111111, 9999999999),
                    'address'     => 'Indonesia',
                    'is_active'   => true,
                ]);

                // Create health checks untuk 7 hari terakhir
                $this->createHealthChecks($crew, $vessel, $coordinator);
            }
        }
    }

    // ... (Keep the rest of your functions: createHealthChecks, generateTemperature, etc. exactly as they were) ...

    private function createHealthChecks(CrewMember $crew, Vessel $vessel, User $reporter)
    {
        // Copy your existing logic here...
        // For brevity, assuming the previous code for helper functions remains the same.

        // Generate untuk 7 hari terakhir
        for ($day = 0; $day < 7; $day++) {
            $checkDate = now()->subDays($day);
            $isNormal = rand(1, 100) <= 70;

            $healthCheck = HealthCheck::create([
                'id' => Str::uuid(),
                'crew_member_id' => $crew->id,
                'vessel_id' => $vessel->id,
                'reported_by' => $reporter->id,
                'checked_at' => $checkDate->setTime(rand(6, 8), rand(0, 59)),
                'check_date' => $checkDate->format('Y-m-d'),
                'work_area' => $vessel->name,
                'temperature' => $this->generateTemperature($isNormal),
                'pulse_rate' => $this->generatePulseRate($isNormal),
                'blood_pressure' => $this->generateBloodPressure($isNormal),
                'respiratory_rate' => $this->generateRespiratoryRate($isNormal),
                'blood_sugar_level' => $this->generateBloodSugar($isNormal),
                'oxygen_saturation' => $this->generateOxygenSaturation($isNormal),
                'illness_complaints' => $isNormal ? 'Tidak ada keluhan' : $this->generateComplaint(),
                'medications_consumed' => $isNormal ? 'Tidak ada' : $this->generateMedication(),
                'fatigue_level' => $isNormal ? 'Ringan' : ['Sedang', 'Berat'][rand(0, 1)],
                'napza_test_result' => rand(1, 100) <= 95 ? 'negative' : 'non_negative',
                'romberg_test_result' => rand(1, 100) <= 98 ? 'negative' : 'positive',
                'remarks' => $isNormal ? null : 'Perlu monitoring lebih lanjut',
                'status' => $this->getStatusByDay($day),
                'verified_by' => $day < 5 ? $reporter->id : null,
                'validated_by' => $day < 3 ? $reporter->id : null,
            ]);

            $healthCheck->has_health_issue = $healthCheck->hasAbnormalVitals();
            $healthCheck->save();
        }
    }

    private function generateTemperature(bool $isNormal): float { return $isNormal ? round(36.0 + (rand(0, 15) / 10), 1) : round(37.6 + (rand(0, 14) / 10), 1); }
    private function generatePulseRate(bool $isNormal): int { return $isNormal ? rand(60, 120) : rand(121, 150); }
    private function generateBloodPressure(bool $isNormal): string { return $isNormal ? rand(90, 140)."/".rand(60, 90) : rand(141, 180)."/".rand(91, 110); }
    private function generateRespiratoryRate(bool $isNormal): int { return $isNormal ? rand(16, 24) : rand(25, 35); }
    private function generateBloodSugar(bool $isNormal): int { return $isNormal ? rand(70, 200) : rand(201, 300); }
    private function generateOxygenSaturation(bool $isNormal): int { return $isNormal ? rand(95, 100) : rand(85, 94); }
    private function generateComplaint(): string { $c = ['Sakit kepala', 'Pusing', 'Mual', 'Lemas', 'Batuk', 'Demam']; return $c[array_rand($c)]; }
    private function generateMedication(): string { $m = ['Paracetamol', 'Vitamin C', 'Obat batuk']; return $m[array_rand($m)]; }
    private function getStatusByDay(int $daysAgo): string { if ($daysAgo <= 1) return 'pending'; elseif ($daysAgo <= 4) return 'reviewed'; else return 'validated'; }
}
