<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EntityFunction;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan semua Role tersedia
        $roles = [
            'super-admin',
            'rses',
            'hsse',
            'nurse',
            'user',
            'koordinator',
            'crew-kapal'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Helper untuk mengambil ID Entity Function
        $getEntityId = fn($name) => EntityFunction::where('name', $name)->value('id');

        // --- DEFINISI ENTITY FUNCTION SESUAI EntityFunctionSeeder BARU ---
        $ENTITY_ROOT      = 'Asst. Manager Swamp Operations';
        $ENTITY_SITE_OPS  = 'Supt. Site Operations Support';
        $ENTITY_FLEET_OPS = 'Supt. Fleet Operations Support';
        $ENTITY_LAND_SM   = 'Land, SM (Bravo 2, Bravo 3)'; // Small Marine / West
        $ENTITY_BM_SPV    = 'BM SPV (Bravo 1)';             // Big Marine / East

        $ENTITY_PLACEHOLDER_PHONE = '085173012307';

        // 3. Definisi Data Users (Mapping Baru)
        $usersData = [
            // --- SUPER ADMIN & HSSE (Mapping ke Root/Management) ---
            [
                'name' => 'Super Admin PFM',
                'email' => 'phm.sup.hse@pertamina.com',
                'phone' => $ENTITY_PLACEHOLDER_PHONE,
                'jabatan' => 'Super Admin',
                'role' => 'super-admin',
                'entity_name' => $ENTITY_ROOT,
            ],
            [
                'name' => 'Super Admin HSSE',
                'email' => 'phm.hsse.wilma@pertamina.com',
                'phone' => '08125423038',
                'jabatan' => 'Super Admin HSSE',
                'role' => 'super-admin',
                'entity_name' => $ENTITY_ROOT, // HSSE biasanya di level atas
            ],

            // --- KOORDINATOR ---
            [
                'name' => 'Koordinator PFM (General)',
                'email' => 'phm.koordinator@pertamina.com',
                'phone' => $ENTITY_PLACEHOLDER_PHONE,
                'jabatan' => 'Koordinator',
                'role' => 'koordinator',
                'entity_name' => $ENTITY_ROOT,
            ],
            [
                'name' => 'Koordinator Site Ops',
                'email' => 'phm.koordinator.site@pertamina.com',
                'phone' => $ENTITY_PLACEHOLDER_PHONE,
                'jabatan' => 'Koordinator Site',
                'role' => 'koordinator',
                'entity_name' => $ENTITY_SITE_OPS,
            ],

            // --- MANAGEMENT & SUPPORT ---
            [
                'name' => 'RSES User',
                'email' => 'phm.po.sup.swa@pertamina.com',
                'phone' => '08125412018',
                'jabatan' => 'RSES',
                'role' => 'rses',
                'entity_name' => $ENTITY_FLEET_OPS, // Mapping RSES ke Fleet Ops (Asumsi)
            ],
            [
                'name' => 'HSSE Officer Wilma',
                'email' => 'phm.hsse.wilma.officer@pertamina.com',
                'phone' => '08125423038',
                'jabatan' => 'HSSE Officer',
                'role' => 'hsse',
                'entity_name' => $ENTITY_ROOT,
            ],
            [
                'name' => 'Nurse User',
                'email' => 'phm.hsse.wilma.nurse@pertamina.com',
                'phone' => $ENTITY_PLACEHOLDER_PHONE,
                'jabatan' => 'Nurse',
                'role' => 'nurse',
                'entity_name' => $ENTITY_ROOT,
            ],

            // --- USER GENERAL (SUPERVISOR LEVEL) ---
            [
                'name' => 'user SWA West (General)',
                'email' => 'phm.po.sup.swa.west@pertamina.com',
                'phone' => '08115426940',
                'jabatan' => 'user',
                'role' => 'user',
                'entity_name' => $ENTITY_SITE_OPS, // Mapping West ke Site Ops
            ],
            [
                'name' => 'user SWA East (General)',
                'email' => 'phm.po.sup.swa.east@pertamina.com',
                'phone' => '0811592424',
                'jabatan' => 'user',
                'role' => 'user',
                'entity_name' => $ENTITY_FLEET_OPS, // Mapping East ke Fleet Ops
            ],

            // --- USER MARINE (Specific Locations) ---
            [
                'name' => 'User Small Marine (Bravo 3)',
                'email' => 'phm.sup.swa.smallmarine.supv.hca@pertamina.com',
                'phone' => '08115409161',
                'jabatan' => 'user Bravo 3',
                'role' => 'user',
                'entity_name' => $ENTITY_LAND_SM, // Mapping ke Small Marine
            ],
            [
                'name' => 'User Big Marine (Bravo 1)',
                'email' => 'phm.sup.swa.bigmarine.supv@pertamina.com',
                'phone' => '08115426942',
                'jabatan' => 'user Bravo 1',
                'role' => 'user',
                'entity_name' => $ENTITY_BM_SPV, // Mapping ke Big Marine
            ],

            // --- CREW KAPAL ---
            [
                'name' => 'Crew Kapal Small Marine',
                'email' => 'crew.smallmarine@pertamina.com',
                'phone' => $ENTITY_PLACEHOLDER_PHONE,
                'jabatan' => 'Crew Kapal',
                'role' => 'crew-kapal',
                'entity_name' => $ENTITY_LAND_SM,
            ],
            [
                'name' => 'Crew Kapal Big Marine',
                'email' => 'crew.bigmarine@pertamina.com',
                'phone' => $ENTITY_PLACEHOLDER_PHONE,
                'jabatan' => 'Crew Kapal',
                'role' => 'crew-kapal',
                'entity_name' => $ENTITY_BM_SPV,
            ],
        ];

        // 4. Eksekusi pembuatan User
        foreach ($usersData as $userData) {
            // Hilangkan spasi dari phone number
            $cleanPhone = str_replace(' ', '', $userData['phone']);

            $entityId = $getEntityId($userData['entity_name']);

            if (!$entityId) {
                // Warning jika EntityFunction belum dibuat (pastikan EntityFunctionSeeder berjalan duluan)
                $this->command->warn("Entity '{$userData['entity_name']}' not found. Skipping {$userData['email']}.");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'phone' => $cleanPhone,
                    'jabatan' => $userData['jabatan'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'entity_function_id' => $entityId,
                ]
            );

            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }

        $this->command->info('Database seeded successfully (Roles and Users with New Entity Mapping).');
    }
}
