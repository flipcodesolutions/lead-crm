<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@crm.com')->first();
        $manager = User::where('email', 'manager@crm.com')->first();
        $telecaller = User::where('email', 'telecaller@crm.com')->first();
        $salesUser = User::where('email', 'sales@crm.com')->first();
        $priya = User::where('email', 'priya@crm.com')->first();

        $stageNew = LeadStage::where('name', 'New')->first();
        $stageContacted = LeadStage::where('name', 'Contacted')->first();
        $stageQual = LeadStage::where('name', 'Qualification')->first();
        $stageProposal = LeadStage::where('name', 'Proposal')->first();
        $stageNeg = LeadStage::where('name', 'Negotiation')->first();
        $stageWon = LeadStage::where('name', 'Won')->first();
        $stageLost = LeadStage::where('name', 'Lost')->first();

        $statusNew = LeadStatus::where('name', 'New')->first();
        $statusContacted = LeadStatus::where('name', 'Contacted')->first();
        $statusInterested = LeadStatus::where('name', 'Interested')->first();
        $statusQualified = LeadStatus::where('name', 'Qualified')->first();
        $statusConverted = LeadStatus::where('name', 'Converted')->first();
        $statusLost = LeadStatus::where('name', 'Lost')->first();

        $sourceIndiaMart = LeadSource::where('name', 'IndiaMART')->first() ?? LeadSource::first();
        $sourceJustdial = LeadSource::where('name', 'Justdial')->first() ?? LeadSource::first();
        $sourceWhatsApp = LeadSource::where('name', 'WhatsApp Business')->first() ?? LeadSource::first();
        $sourceWeb = LeadSource::where('name', 'Website')->first() ?? LeadSource::first();
        $sourceReferral = LeadSource::where('name', 'Referral')->first() ?? LeadSource::first();

        $services = Service::all();

        // 1. Lead: Reliance Logistics Gujarat (Won Deal - ₹1,45,000)
        $lead1 = Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-0001',
            'name' => 'Vikram Singhania',
            'company_name' => 'Reliance Logistics Gujarat Pvt. Ltd.',
            'email' => 'vikram.s@reliancelogistics.in',
            'phone' => '+91 98251 23456',
            'alternate_phone' => '+91 98251 98765',
            'address' => 'Plot 45, GIDC Industrial Estate, Naroda',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'country' => 'India',
            'source_id' => $sourceIndiaMart?->id,
            'status_id' => $statusConverted?->id,
            'stage_id' => $stageWon?->id,
            'assigned_to' => $salesUser?->id,
            'created_by' => $admin?->id,
            'expected_value' => 145000.00,
            'description' => 'Requirement for 35 fleet managers with WhatsApp automated dispatch notifications.',
            'converted_at' => Carbon::now()->subDays(5),
            'status' => 1,
            'created_at' => Carbon::now()->subDays(12),
        ]);

        LeadAssignment::create([
            'lead_id' => $lead1->id,
            'assigned_by' => $manager?->id,
            'assigned_to' => $salesUser?->id,
            'assigned_at' => Carbon::now()->subDays(11),
            'remarks' => 'Assigned to Western Zone Enterprise Representative.',
        ]);

        LeadNote::create([
            'lead_id' => $lead1->id,
            'user_id' => $salesUser?->id,
            'note' => 'Client signed purchase order. 50% advance received via NEFT.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // Opportunity 1 (Won)
        $opp1 = Opportunity::create([
            'lead_id' => $lead1->id,
            'opportunity_number' => 'OPP-' . date('Ymd') . '-0001',
            'name' => 'Reliance Logistics Fleet CRM & WhatsApp',
            'customer_name' => 'Vikram Singhania',
            'company_name' => 'Reliance Logistics Gujarat Pvt. Ltd.',
            'expected_revenue' => 145000.00,
            'probability' => 100,
            'expected_closing_date' => Carbon::now()->subDays(1),
            'stage_id' => $stageWon?->id,
            'assigned_to' => $salesUser?->id,
            'description' => 'Full deployment of 35 user licenses with WhatsApp Business API.',
            'status' => 'Won',
            'won_at' => Carbon::now()->subDays(1),
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // Quotation 1 (Accepted)
        $quote1 = Quotation::create([
            'quotation_number' => 'QT-' . date('Ymd') . '-0001',
            'opportunity_id' => $opp1->id,
            'lead_id' => $lead1->id,
            'customer_name' => 'Vikram Singhania',
            'customer_email' => 'vikram.s@reliancelogistics.in',
            'customer_phone' => '+91 98251 23456',
            'quotation_date' => Carbon::now()->subDays(4),
            'valid_until' => Carbon::now()->addDays(26),
            'subtotal' => 125000.00,
            'tax_amount' => 22500.00,
            'discount_amount' => 5000.00,
            'total_amount' => 142500.00,
            'notes' => 'Prices inclusive of 18% GST. Full technical support included for 12 months.',
            'terms_conditions' => "1. 50% advance along with PO.\n2. Balance within 15 days of deployment.\n3. GSTIN: 24AAACA1234A1Z5 applicable.",
            'status' => 'Accepted',
            'created_by' => $salesUser?->id,
        ]);

        if ($services->count() >= 2) {
            QuotationItem::create([
                'quotation_id' => $quote1->id,
                'service_id' => $services[0]->id,
                'description' => 'CRM Cloud Enterprise License (Annual) - Fleet Edition',
                'quantity' => 2,
                'price' => 45000.00,
                'discount' => 2500.00,
                'tax_percentage' => 18.00,
                'total' => 103250.00,
            ]);
            QuotationItem::create([
                'quotation_id' => $quote1->id,
                'service_id' => $services[1]->id,
                'description' => 'Complete Implementation & Onboarding Workshop',
                'quantity' => 1,
                'price' => 25000.00,
                'discount' => 2500.00,
                'tax_percentage' => 18.00,
                'total' => 26550.00,
            ]);
        }

        // 2. Lead: Sun Healthcare Pharma Mumbai (Negotiation Stage - ₹85,000)
        $lead2 = Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-0002',
            'name' => 'Dr. Ananya Joshi',
            'company_name' => 'Sun Healthcare Pharma Ltd.',
            'email' => 'ananya.joshi@sunhealth.co.in',
            'phone' => '+91 98190 88776',
            'address' => 'Floor 8, Peninsula Tower, Lower Parel',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
            'source_id' => $sourceJustdial?->id,
            'status_id' => $statusQualified?->id,
            'stage_id' => $stageNeg?->id,
            'assigned_to' => $salesUser?->id,
            'created_by' => $telecaller?->id,
            'expected_value' => 85000.00,
            'description' => 'Inquiry from Justdial for Medical Representative tracking and doctor sample distribution.',
            'next_follow_up_at' => Carbon::today()->addHours(15),
            'status' => 1,
            'created_at' => Carbon::now()->subDays(6),
        ]);

        FollowUp::create([
            'lead_id' => $lead2->id,
            'user_id' => $salesUser?->id,
            'type' => 'Call',
            'follow_up_date' => Carbon::today(),
            'follow_up_time' => '15:00',
            'subject' => 'Commercial Discussion with Dr. Ananya',
            'description' => 'Discuss 5% discount on WhatsApp MR module and 2-year contract terms.',
            'status' => 'Pending',
        ]);

        $opp2 = Opportunity::create([
            'lead_id' => $lead2->id,
            'opportunity_number' => 'OPP-' . date('Ymd') . '-0002',
            'name' => 'Sun Healthcare MR Tracking & WhatsApp Suite',
            'customer_name' => 'Dr. Ananya Joshi',
            'company_name' => 'Sun Healthcare Pharma Ltd.',
            'expected_revenue' => 85000.00,
            'probability' => 80,
            'expected_closing_date' => Carbon::now()->addDays(4),
            'stage_id' => $stageNeg?->id,
            'assigned_to' => $salesUser?->id,
            'description' => 'Final commercial review with procurement head.',
            'status' => 'Open',
            'created_at' => Carbon::now()->subDays(4),
        ]);

        $quote2 = Quotation::create([
            'quotation_number' => 'QT-' . date('Ymd') . '-0002',
            'opportunity_id' => $opp2->id,
            'lead_id' => $lead2->id,
            'customer_name' => 'Dr. Ananya Joshi',
            'customer_email' => 'ananya.joshi@sunhealth.co.in',
            'customer_phone' => '+91 98190 88776',
            'quotation_date' => Carbon::now()->subDays(2),
            'valid_until' => Carbon::now()->addDays(20),
            'subtotal' => 75000.00,
            'tax_amount' => 13500.00,
            'discount_amount' => 3500.00,
            'total_amount' => 85000.00,
            'notes' => 'Includes 18% GST. Medical MR field module included.',
            'status' => 'Sent',
            'created_by' => $salesUser?->id,
        ]);

        // 3. Lead: Surat Diamond & Textile Expo (Proposal - ₹60,000)
        $lead3 = Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-0003',
            'name' => 'Harshil Choksi',
            'company_name' => 'Choksi Textiles & Fashion Surat',
            'email' => 'harshil@choksitextiles.com',
            'phone' => '+91 98790 33445',
            'address' => 'Ring Road Textile Market, Shop 102',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'country' => 'India',
            'source_id' => $sourceWhatsApp?->id,
            'status_id' => $statusInterested?->id,
            'stage_id' => $stageProposal?->id,
            'assigned_to' => $priya?->id,
            'created_by' => $telecaller?->id,
            'expected_value' => 60000.00,
            'description' => 'WhatsApp catalog inquiry for B2B wholesale order tracking.',
            'next_follow_up_at' => Carbon::tomorrow()->setHour(11),
            'status' => 1,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        FollowUp::create([
            'lead_id' => $lead3->id,
            'user_id' => $priya?->id,
            'type' => 'Meeting',
            'follow_up_date' => Carbon::tomorrow(),
            'follow_up_time' => '11:30',
            'subject' => 'Product Demo & WhatsApp Catalog Integration',
            'description' => 'Demonstrate automated WhatsApp quotation and catalog order flow.',
            'status' => 'Pending',
        ]);

        Opportunity::create([
            'lead_id' => $lead3->id,
            'opportunity_number' => 'OPP-' . date('Ymd') . '-0003',
            'name' => 'Choksi Textiles WhatsApp Catalog & CRM',
            'customer_name' => 'Harshil Choksi',
            'company_name' => 'Choksi Textiles & Fashion Surat',
            'expected_revenue' => 60000.00,
            'probability' => 65,
            'expected_closing_date' => Carbon::now()->addDays(10),
            'stage_id' => $stageProposal?->id,
            'assigned_to' => $priya?->id,
            'description' => 'Quotation submitted for 15 wholesale sales reps.',
            'status' => 'Open',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 4. Lead: Bangalore Cloud Analytics (New Inbound Lead - ₹95,000)
        $lead4 = Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-0004',
            'name' => 'Karthik Ramanathan',
            'company_name' => 'Vayu Analytics Bengaluru',
            'email' => 'karthik@vayuanalytics.in',
            'phone' => '+91 99000 66778',
            'address' => 'Indiranagar 100ft Road',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'country' => 'India',
            'source_id' => $sourceWeb?->id,
            'status_id' => $statusNew?->id,
            'stage_id' => $stageNew?->id,
            'assigned_to' => $telecaller?->id,
            'created_by' => $admin?->id,
            'expected_value' => 95000.00,
            'description' => 'Inbound website demo request for lead qualification and custom pipeline.',
            'next_follow_up_at' => Carbon::today()->addHours(11),
            'status' => 1,
            'created_at' => Carbon::today()->subHours(2),
        ]);

        FollowUp::create([
            'lead_id' => $lead4->id,
            'user_id' => $telecaller?->id,
            'type' => 'Call',
            'follow_up_date' => Carbon::today(),
            'follow_up_time' => '11:00',
            'subject' => 'First Discovery Call - Karthik Ramanathan',
            'description' => 'Understand current sales team size and integration with payment gateways.',
            'status' => 'Pending',
        ]);

        // Notifications
        Notification::create([
            'user_id' => $salesUser?->id,
            'title' => 'Quotation Accepted (₹1,42,500)',
            'message' => 'Reliance Logistics Gujarat accepted Quotation #QT-' . date('Ymd') . '-0001',
            'type' => 'success',
            'link' => route('quotations.show', $quote1->id),
        ]);

        Notification::create([
            'user_id' => $telecaller?->id,
            'title' => 'New IndiaMART Inquiry Assigned',
            'message' => 'New inquiry from Karthik Ramanathan (Vayu Analytics)',
            'type' => 'info',
            'link' => route('leads.show', $lead4->id),
        ]);
    }
}
