<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ContactPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permission if not exists
        $permission = Permission::firstOrCreate(
            ['name' => 'manage contacts'],
            ['guard_name' => 'web']
        );

        // Assign to super-admin
        $superAdmin = Role::findByName('super-admin', 'web');
        if ($superAdmin && !$superAdmin->hasPermissionTo('manage contacts')) {
            $superAdmin->givePermissionTo($permission);
        }

        $this->command->info('Permission "manage contacts" seeded successfully.');
        $this->command->info('Assigned to role: super-admin');
    }
}
