<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // --- AUTH / ROLE ---
            RolePermissionSeeder::class,
            EntityFunctionSeeder::class,
            CompanySeeder::class,
            UserSeeder::class,
            // --- MASTER DATA ---

            AreaSeeder::class,
            PelanggaranSeeder::class,
            UnsafeActSeeder::class,
            UnsafeConditionSeeder::class,
            ActionCategorySeeder::class,
            // LogindoSeeder::class,
            // CampaignSeeder::class,
            // ContractSeeder::class,
            SecurityEventCategorySeeder::class,
        ]);
    }
}
