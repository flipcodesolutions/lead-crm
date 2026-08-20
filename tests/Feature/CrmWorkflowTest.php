<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_crm_full_lifecycle_workflow(): void
    {
        $admin = User::where('email', 'admin@crm.com')->first();
        $this->assertNotNull($admin);

        // 1. Dashboard loads
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('CRM Executive Dashboard');

        // 2. Create Lead
        $source = LeadSource::first();
        $status = LeadStatus::first();
        $stage = LeadStage::orderBy('sort_order')->first();

        $leadResponse = $this->actingAs($admin)->post('/leads', [
            'name' => 'TechCorp Global',
            'phone' => '+1 (555) 998-0011',
            'company_name' => 'TechCorp Solutions',
            'email' => 'contact@techcorp.com',
            'expected_value' => 5000.00,
            'source_id' => $source->id,
            'status_id' => $status->id,
            'stage_id' => $stage->id,
            'assigned_to' => $admin->id,
            'description' => 'Interested in 20 enterprise licenses and custom onboarding.',
        ]);

        $lead = Lead::where('email', 'contact@techcorp.com')->first();
        $this->assertNotNull($lead);
        $this->assertStringStartsWith('LD-', $lead->lead_number);
        $leadResponse->assertRedirect(route('leads.show', $lead->id));

        // 3. Add Follow-up & Activity & Note
        $this->actingAs($admin)->post("/leads/{$lead->id}/follow-ups", [
            'type' => 'Call',
            'follow_up_date' => date('Y-m-d'),
            'follow_up_time' => '15:00',
            'subject' => 'Initial Discovery Call with TechCorp',
            'description' => 'Discuss technical requirements.',
        ])->assertStatus(302);

        $this->assertDatabaseHas('follow_ups', [
            'lead_id' => $lead->id,
            'subject' => 'Initial Discovery Call with TechCorp',
        ]);

        $this->actingAs($admin)->post("/leads/{$lead->id}/notes", [
            'note' => 'Discovery call completed. Customer has approved technical spec.',
        ])->assertStatus(302);

        $this->assertDatabaseHas('lead_notes', [
            'lead_id' => $lead->id,
            'note' => 'Discovery call completed. Customer has approved technical spec.',
        ]);

        // 4. Qualify Lead & Convert to Opportunity
        $this->actingAs($admin)->post("/leads/{$lead->id}/qualify")->assertStatus(302);

        $oppResponse = $this->actingAs($admin)->post("/leads/{$lead->id}/convert", [
            'name' => 'TechCorp 20 Licenses Deal',
            'expected_revenue' => 5000.00,
            'probability' => 75,
            'expected_closing_date' => now()->addDays(10)->toDateString(),
            'stage_id' => $stage->id,
            'assigned_to' => $admin->id,
        ]);

        $opp = Opportunity::where('lead_id', $lead->id)->first();
        $this->assertNotNull($opp);
        $this->assertStringStartsWith('OPP-', $opp->opportunity_number);
        $oppResponse->assertRedirect(route('opportunities.show', $opp->id));

        // 5. Kanban Stage Update via AJAX
        $proposalStage = LeadStage::where('name', 'Proposal')->first();
        $ajaxResponse = $this->actingAs($admin)->postJson("/opportunities/{$opp->id}/stage-ajax", [
            'stage_id' => $proposalStage->id,
        ]);
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJson(['success' => true]);
        $this->assertEquals($proposalStage->id, $opp->fresh()->stage_id);

        // 6. Create Quotation with Items
        $service = Service::first();
        $quoteResponse = $this->actingAs($admin)->post('/quotations', [
            'customer_name' => 'TechCorp Global',
            'customer_email' => 'contact@techcorp.com',
            'customer_phone' => '+1 (555) 998-0011',
            'quotation_date' => date('Y-m-d'),
            'valid_until' => now()->addDays(30)->toDateString(),
            'opportunity_id' => $opp->id,
            'lead_id' => $lead->id,
            'discount_amount' => 100.00,
            'notes' => 'Custom proposal with 1 year cloud support.',
            'terms_conditions' => 'Standard enterprise agreement.',
            'status' => 'Draft',
            'items' => [
                [
                    'service_id' => $service?->id,
                    'description' => 'Enterprise License & Implementation',
                    'quantity' => 2,
                    'price' => 1200.00,
                    'discount' => 50.00,
                    'tax_percentage' => 18.00,
                ]
            ]
        ]);

        $quotation = Quotation::where('opportunity_id', $opp->id)->first();
        $this->assertNotNull($quotation);
        $this->assertStringStartsWith('QT-', $quotation->quotation_number);
        $quoteResponse->assertRedirect(route('quotations.show', $quotation->id));

        // 7. Verify PDF Generation
        $pdfResponse = $this->actingAs($admin)->get("/quotations/{$quotation->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type') ?? '');

        // 8. Accept Quotation & Verify Deal Won
        $this->actingAs($admin)->post("/quotations/{$quotation->id}/status", [
            'status' => 'Accepted',
        ])->assertStatus(302);

        $this->assertEquals('Accepted', $quotation->fresh()->status);
        $this->assertEquals('Won', $opp->fresh()->status);
    }
}
