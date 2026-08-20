<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'System administrator with full permissions across all modules, masters, and settings.',
            ],
            [
                'name' => 'Manager',
                'description' => 'Sales manager with team oversight, lead assignment, pipeline monitoring, and report access.',
            ],
            [
                'name' => 'Telecaller',
                'description' => 'Inbound/outbound caller handling lead qualification, follow-ups, and notes.',
            ],
            [
                'name' => 'Salesperson',
                'description' => 'Sales representative managing assigned leads, opportunities, quotations, and closing deals.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
