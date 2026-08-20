<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Illuminate\Database\Seeder;

class LeadStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'New',
            'Contacted',
            'Interested',
            'Not Interested',
            'Qualified',
            'Unqualified',
            'Converted',
            'Lost',
        ];

        foreach ($statuses as $status) {
            LeadStatus::firstOrCreate(['name' => $status], [
                'name' => $status,
                'status' => 1,
            ]);
        }
    }
}
