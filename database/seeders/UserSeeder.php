<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $telecallerRole = Role::where('name', 'Telecaller')->first();
        $salesRole = Role::where('name', 'Salesperson')->first();

        $users = [
            [
                'name' => 'Rajesh Sharma (Admin)',
                'email' => 'admin@crm.com',
                'phone' => '+91 98250 11111',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole?->id,
                'status' => 1,
            ],
            [
                'name' => 'Pooja Patel (Manager)',
                'email' => 'manager@crm.com',
                'phone' => '+91 98250 22222',
                'password' => Hash::make('password123'),
                'role_id' => $managerRole?->id,
                'status' => 1,
            ],
            [
                'name' => 'Neha Verma (Telecaller)',
                'email' => 'telecaller@crm.com',
                'phone' => '+91 98250 33333',
                'password' => Hash::make('password123'),
                'role_id' => $telecallerRole?->id,
                'status' => 1,
            ],
            [
                'name' => 'Amit Desai (Sales Rep)',
                'email' => 'sales@crm.com',
                'phone' => '+91 98250 44444',
                'password' => Hash::make('password123'),
                'role_id' => $salesRole?->id,
                'status' => 1,
            ],
            [
                'name' => 'Priya Mehta (Sales Rep)',
                'email' => 'priya@crm.com',
                'phone' => '+91 98250 55555',
                'password' => Hash::make('password123'),
                'role_id' => $salesRole?->id,
                'status' => 1,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['email' => $userData['email']], $userData);
        }
    }
}
