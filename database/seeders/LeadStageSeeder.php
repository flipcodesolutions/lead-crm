<?php

namespace Database\Seeders;

use App\Models\LeadStage;
use Illuminate\Database\Seeder;

class LeadStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'New', 'sort_order' => 1],
            ['name' => 'Contacted', 'sort_order' => 2],
            ['name' => 'Qualification', 'sort_order' => 3],
            ['name' => 'Proposal', 'sort_order' => 4],
            ['name' => 'Negotiation', 'sort_order' => 5],
            ['name' => 'Won', 'sort_order' => 6],
            ['name' => 'Lost', 'sort_order' => 7],
        ];

        foreach ($stages as $stage) {
            LeadStage::firstOrCreate(['name' => $stage['name']], [
                'name' => $stage['name'],
                'sort_order' => $stage['sort_order'],
                'status' => 1,
            ]);
        }
    }
}
