<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CrewAssessmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view crew assessment',    // semua role — lihat
            'manage crew assessment',  // hsse, super-admin — CRUD
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'super-admin' => ['view crew assessment', 'manage crew assessment'],
            'hsse'        => ['view crew assessment', 'manage crew assessment'],
            'user'        => ['view crew assessment'],
            'coordinator' => ['view crew assessment'],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($perms);
        }
    }
}
