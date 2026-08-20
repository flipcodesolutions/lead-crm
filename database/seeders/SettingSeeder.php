<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'Apex Infotech Solutions Pvt. Ltd.',
            'company_email' => 'contact@apexinfotech.in',
            'company_phone' => '+91 98250 12345',
            'company_address' => 'B-402, Titanium City Centre, Prahlad Nagar, S.G. Highway, Ahmedabad, Gujarat 380015',
            'currency_symbol' => '₹',
            'tax_number' => 'GSTIN: 24AAACA1234A1Z5',
            'quotation_prefix' => 'QT-',
            'lead_prefix' => 'LD-',
            'terms_and_conditions' => "1. Quotation Validity: Valid for 30 days from date of issue.\n2. Payment Terms: 50% advance upon contract signing, 50% upon milestone completion.\n3. Taxes: Applicable GST (18%) is extra as stated above.\n4. Support: Standard 9x6 business hours support included with initial deployment.\n5. Jurisdiction: Subject to Ahmedabad, India jurisdiction only.",
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
