<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Dashboard Permissions
            'view dcu dashboard',
            'view cermat dashboard',

            // Profile Permissions
            'update profile',
            'update profile company',

            // Management Permissions
            'manage roles',
            'manage companies',
            'manage security event categories',
            'manage unsafe acts',
            'manage unsafe conditions',
            'manage areas',
            'manage pelanggaran',
            'manage action categories',
            'manage groups',
            'manage vessels',
            'manage users',
            'manage entity functions',
            'manage campaigns',
            'manage daily checkup',
            'manage cover templates',
            'manage campaign salman',
            'manage broadcast',
            'manage my action',
            'manage cermat',
            'manage contracts',
            'manage report salman',
            'manage kpi hsse',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Create Roles and Assign Permissions

        // 1. Super Admin Role - Full Access
        $adminRole = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['guard_name' => 'web']
        );
        $adminRole->givePermissionTo(Permission::all());

        // 2. User Role
        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['guard_name' => 'web']
        );
        $userRole->givePermissionTo([
            'view dcu dashboard',
            'view cermat dashboard',
            'update profile',
            'manage my action',
            'manage cermat',
            'manage campaign salman',
        ]);

        // 3. Crew Kapal Role
        $crewRole = Role::firstOrCreate(
            ['name' => 'crew-kapal'],
            ['guard_name' => 'web']
        );
        $crewRole->givePermissionTo([
            'view dcu dashboard',
            'view cermat dashboard',
            'update profile',
            'manage my action', // DITAMBAHKAN
            'manage cermat',    // DITAMBAHKAN
            'manage campaign salman',
        ]);

        // 4. Nurse Role
        $nurseRole = Role::firstOrCreate(
            ['name' => 'nurse'],
            ['guard_name' => 'web']
        );
        $nurseRole->givePermissionTo([
            'view cermat dashboard',
            'view dcu dashboard',
            'update profile',
            'manage my action',
            'manage cermat',
        ]);

        // 5. Koordinator Role
        $koordinatorRole = Role::firstOrCreate(
            ['name' => 'koordinator'],
            ['guard_name' => 'web']
        );
        $koordinatorRole->givePermissionTo([
            'view cermat dashboard',
            'view dcu dashboard',
            'update profile',
            'update profile company',
            'manage my action',
            'manage cermat',
            'manage daily checkup',
        ]);

        // 6. HSSE Role (Optional - if still needed)
        $hsseRole = Role::firstOrCreate(
            ['name' => 'hsse'],
            ['guard_name' => 'web']
        );
        $hsseRole->givePermissionTo([
            'view dcu dashboard',
            'view cermat dashboard',
            'update profile',
            'manage cermat',
            'manage my action',
            'manage broadcast',
            'manage daily checkup',
            'manage campaign salman',
        ]);

        // 7. RSES Role (Optional - if still needed)
        $rsesRole = Role::firstOrCreate(
            ['name' => 'rses'],
            ['guard_name' => 'web']
        );
        $rsesRole->givePermissionTo([
            'view dcu dashboard',
            'view cermat dashboard',
            'update profile',
            'manage cermat',
            'manage my action',
            'manage campaign salman',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Total Permissions: ' . Permission::count());
        $this->command->info('Total Roles: ' . Role::count());
    }
}
