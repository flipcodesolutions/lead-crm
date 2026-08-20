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
            RoleSeeder::class,
            UserSeeder::class,
            LeadSourceSeeder::class,
            LeadStatusSeeder::class,
            LeadStageSeeder::class,
            ServiceSeeder::class,
            SettingSeeder::class,
            DemoCrmSeeder::class,
            HRSeeder::class,
        ]);
    }
}
