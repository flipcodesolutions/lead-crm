<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            'IndiaMART',
            'Justdial',
            'TradeIndia',
            'WhatsApp Business',
            'Website',
            'Google Ads',
            'Referral',
            'Phone Call',
            'LinkedIn',
            'Trade Expo / Exhibition',
            'Walk-in',
            'Other',
        ];

        foreach ($sources as $source) {
            LeadSource::firstOrCreate(['name' => $source], [
                'name' => $source,
                'status' => 1,
            ]);
        }
    }
}
