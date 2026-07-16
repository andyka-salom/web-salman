<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seed semua kebutuhan fitur HSSE Marine Onboard Evaluation:
 *  1. 5 aspek kriteria penilaian (hsse_criteria)
 *  2. Permission 'manage hsse evaluation' + assign ke role terkait
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=HsseOnboardSeeder
 */
class HsseOnboardSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCriteria();
        $this->seedPermission();
    }

    // ================================================================
    // 1. KRITERIA PENILAIAN
    // ================================================================
    private function seedCriteria(): void
    {
        $this->command->info('--- Seeding HSSE Criteria ---');

        $criteria = [
            [
                'order_no' => 1,
                'aspect'   => 'Pemahaman mengenai Golden Rules, CLSR, Kapten Salman',
            ],
            [
                'order_no' => 2,
                'aspect'   => 'Kemampuan menjelaskan prosedur kerja sesuai jabatan di kapal',
            ],
            [
                'order_no' => 3,
                'aspect'   => 'Kemampuan melakukan identifikasi bahaya dan mitigasi '
                            . '(eliminasi/substitusi/engineering/administrasi/APD)',
            ],
            [
                'order_no' => 4,
                'aspect'   => 'Pemahaman mengenai pelaporan anomaly (CerMAT, TEMAN, '
                            . 'Stop work authority) dan kondisi emergency',
            ],
            [
                'order_no' => 5,
                'aspect'   => 'Pemahaman penanganan keadaan darurat di kapal',
            ],
        ];

        foreach ($criteria as $item) {
            $exists = DB::table('hsse_criteria')
                ->where('order_no', $item['order_no'])
                ->exists();

            DB::table('hsse_criteria')->updateOrInsert(
                ['order_no' => $item['order_no']],
                [
                    'id'         => $exists
                        ? DB::table('hsse_criteria')->where('order_no', $item['order_no'])->value('id')
                        : Str::uuid(),
                    'aspect'     => $item['aspect'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->line("  Kriteria #{$item['order_no']}: " .
                ($exists ? 'diperbarui.' : 'dibuat baru.'));
        }

        $this->command->info('HSSE Criteria selesai. Total: ' . count($criteria) . ' aspek.');
    }

    // ================================================================
    // 2. PERMISSION & ROLE ASSIGNMENT
    // ================================================================
    private function seedPermission(): void
    {
        $this->command->info('--- Seeding HSSE Evaluation Permission ---');

        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permission
        $permission = Permission::firstOrCreate(
            ['name' => 'manage hsse evaluation'],
            ['guard_name' => 'web']
        );

        $this->command->line('  Permission "manage hsse evaluation" — ' .
            ($permission->wasRecentlyCreated ? 'dibuat baru.' : 'sudah ada, dilewati.'));

        // Role yang mendapat permission ini
        $roles = [
            'super-admin',
            'hsse',
            'user',
            'koordinator',
        ];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if (!$role) {
                $this->command->warn("  Role \"{$roleName}\" tidak ditemukan, dilewati.");
                continue;
            }

            if ($role->hasPermissionTo('manage hsse evaluation')) {
                $this->command->line("  Role \"{$roleName}\" sudah memiliki permission, dilewati.");
                continue;
            }

            $role->givePermissionTo($permission);
            $this->command->info("  Permission diberikan ke role \"{$roleName}\".");
        }

        // Reset cache setelah semua perubahan
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('HSSE Permission selesai.');
    }
}
