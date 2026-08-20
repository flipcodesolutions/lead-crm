<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'CRM Cloud Enterprise License (Annual)',
                'description' => 'Unlimited users cloud CRM access with sales automation, IndiaMART & Justdial API sync, and role security.',
                'price' => 45000.00,
                'tax_percentage' => 18.00,
                'status' => 1,
            ],
            [
                'name' => 'Complete Implementation & Onboarding Package',
                'description' => 'End-to-end configuration, pipeline customization, team training workshops, and historical data migration.',
                'price' => 25000.00,
                'tax_percentage' => 18.00,
                'status' => 1,
            ],
            [
                'name' => 'WhatsApp Business & Telephony Integration Module',
                'description' => 'Official WhatsApp Business API integration with automated message templates and click-to-call logging.',
                'price' => 15000.00,
                'tax_percentage' => 18.00,
                'status' => 1,
            ],
            [
                'name' => 'Annual Dedicated Technical Support & Maintenance SLA',
                'description' => 'Priority response time under 1 hour, dedicated account manager, and monthly CRM health checkups.',
                'price' => 18000.00,
                'tax_percentage' => 18.00,
                'status' => 1,
            ],
            [
                'name' => 'Custom Tally / Busy ERP Connector Integration',
                'description' => 'Automated invoice and ledger synchronization between CRM quotations and Tally/Busy accounting software.',
                'price' => 35000.00,
                'tax_percentage' => 18.00,
                'status' => 1,
            ],
        ];

        foreach ($services as $svc) {
            Service::updateOrCreate(['name' => $svc['name']], $svc);
        }
    }
}
