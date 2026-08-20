<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveCrmTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $telecaller;
    protected User $salesperson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@crm.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@crm.com')->firstOrFail();
        $this->telecaller = User::where('email', 'telecaller@crm.com')->firstOrFail();
        $this->salesperson = User::where('email', 'sales@crm.com')->firstOrFail();
    }

    /**
     * 1. Test Role-Based Access Control on Admin Masters
     */
    public function test_non_admin_cannot_access_admin_masters(): void
    {
        // Manager should get 403 on admin routes
        $this->actingAs($this->manager)->get('/admin/users')->assertStatus(403);
        $this->actingAs($this->manager)->get('/admin/roles')->assertStatus(403);
        $this->actingAs($this->manager)->get('/admin/settings')->assertStatus(403);

        // Telecaller should get 403
        $this->actingAs($this->telecaller)->get('/admin/users')->assertStatus(403);
        $this->actingAs($this->telecaller)->get('/admin/services')->assertStatus(403);

        // Salesperson should get 403
        $this->actingAs($this->salesperson)->get('/admin/lead-sources')->assertStatus(403);
        $this->actingAs($this->salesperson)->get('/admin/lead-stages')->assertStatus(403);

        // Admin can access all admin masters
        $this->actingAs($this->admin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/roles')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/lead-sources')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/lead-statuses')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/lead-stages')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/services')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/settings')->assertStatus(200);
    }

    /**
     * 2. Test Admin Users CRUD
     */
    public function test_admin_users_crud(): void
    {
        $role = Role::where('name', 'Salesperson')->firstOrFail();

        // Create User
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'New Test Agent',
            'email' => 'agent@crm.com',
            'phone' => '+1 555-4444',
            'role_id' => $role->id,
            'password' => 'secret123',
            'status' => 1,
        ]);
        $response->assertRedirect('/admin/users');

        $newUser = User::where('email', 'agent@crm.com')->firstOrFail();
        $this->assertEquals('New Test Agent', $newUser->name);

        // Edit & Update User
        $this->actingAs($this->admin)->get("/admin/users/{$newUser->id}/edit")->assertStatus(200);
        $this->actingAs($this->admin)->put("/admin/users/{$newUser->id}", [
            'name' => 'Updated Agent Name',
            'email' => 'agent@crm.com',
            'phone' => '+1 555-9999',
            'role_id' => $role->id,
            'status' => 1,
        ])->assertRedirect('/admin/users');

        $this->assertEquals('Updated Agent Name', $newUser->fresh()->name);

        // Delete User
        $this->actingAs($this->admin)->delete("/admin/users/{$newUser->id}")->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }

    /**
     * 3. Test Admin Masters (Sources, Statuses, Stages, Services, Settings) CRUD
     */
    public function test_admin_masters_crud(): void
    {
        // Lead Source
        $this->actingAs($this->admin)->post('/admin/lead-sources', [
            'name' => 'Conference Event',
            'status' => 1,
        ])->assertRedirect('/admin/lead-sources');
        $source = LeadSource::where('name', 'Conference Event')->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/lead-sources/{$source->id}", [
            'name' => 'Tech Conference 2026',
            'status' => 1,
        ])->assertRedirect('/admin/lead-sources');
        $this->assertEquals('Tech Conference 2026', $source->fresh()->name);

        // Lead Status
        $this->actingAs($this->admin)->post('/admin/lead-statuses', [
            'name' => 'Demo Scheduled',
            'status' => 1,
        ])->assertRedirect('/admin/lead-statuses');
        $status = LeadStatus::where('name', 'Demo Scheduled')->firstOrFail();

        // Lead Stage
        $this->actingAs($this->admin)->post('/admin/lead-stages', [
            'name' => 'Legal Review',
            'sort_order' => 8,
            'status' => 1,
        ])->assertRedirect('/admin/lead-stages');
        $stage = LeadStage::where('name', 'Legal Review')->firstOrFail();

        // Service Catalog
        $this->actingAs($this->admin)->post('/admin/services', [
            'name' => 'Cybersecurity Audit Addon',
            'description' => 'Annual pen-testing & audit report.',
            'price' => 1500.00,
            'tax_percentage' => 18.00,
            'status' => 1,
        ])->assertRedirect('/admin/services');
        $service = Service::where('name', 'Cybersecurity Audit Addon')->firstOrFail();

        // Settings update
        $this->actingAs($this->admin)->post('/admin/settings', [
            'company_name' => 'Apex Enterprise Systems',
            'currency_symbol' => '€',
            'company_email' => 'hq@apexenterprise.com',
        ])->assertStatus(302);
        $this->assertEquals('Apex Enterprise Systems', Setting::get('company_name'));
        $this->assertEquals('€', Setting::get('currency_symbol'));
    }

    /**
     * 4. Test Lead Lifecycle, Filters, Details, and Conversions
     */
    public function test_lead_lifecycle_and_actions(): void
    {
        // View Leads Index with Search & Filters
        $this->actingAs($this->salesperson)->get('/leads')->assertStatus(200);
        $this->actingAs($this->salesperson)->get('/leads?search=Zenith')->assertStatus(200);
        $this->actingAs($this->salesperson)->get('/leads?stage_id=1&status_id=1')->assertStatus(200);

        // Create Lead
        $source = LeadSource::first();
        $status = LeadStatus::first();
        $stage = LeadStage::first();

        $createResponse = $this->actingAs($this->telecaller)->post('/leads', [
            'name' => 'Acme Corporation',
            'company_name' => 'Acme Global',
            'email' => 'info@acmeglobal.com',
            'phone' => '+1 (555) 777-8888',
            'address' => '500 Market St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'country' => 'USA',
            'expected_value' => 8500.00,
            'source_id' => $source->id,
            'status_id' => $status->id,
            'stage_id' => $stage->id,
            'assigned_to' => $this->telecaller->id,
            'description' => 'Looking for CRM automation and VoIP integration.',
        ]);

        $lead = Lead::where('email', 'info@acmeglobal.com')->firstOrFail();
        $createResponse->assertRedirect(route('leads.show', $lead->id));

        // View Lead Show Page
        $this->actingAs($this->telecaller)->get("/leads/{$lead->id}")
            ->assertStatus(200)
            ->assertSee('Acme Corporation')
            ->assertSee('Acme Global');

        // Reassign Lead to Salesperson
        $this->actingAs($this->manager)->post("/leads/{$lead->id}/assign", [
            'assigned_to' => $this->salesperson->id,
            'remarks' => 'Handed over to enterprise sales representative.',
        ])->assertStatus(302);
        $this->assertEquals($this->salesperson->id, $lead->fresh()->assigned_to);

        // Add Follow-up
        $this->actingAs($this->salesperson)->post("/leads/{$lead->id}/follow-ups", [
            'type' => 'Meeting',
            'follow_up_date' => date('Y-m-d'),
            'follow_up_time' => '10:00',
            'subject' => 'System Architecture Review',
            'description' => 'Detailed product walkthrough.',
        ])->assertStatus(302);
        $followUp = FollowUp::where('lead_id', $lead->id)->firstOrFail();

        // Complete Follow-up with automatic email dispatch
        $this->actingAs($this->salesperson)->post("/follow-ups/{$followUp->id}/complete", [
            'result' => 'Client agreed to commercial proposal terms for 20 licenses.',
            'next_follow_up_date' => now()->addDays(3)->toDateString(),
            'next_follow_up_time' => '14:00',
            'send_email' => 1,
            'recipient_email' => 'info@acmeglobal.com',
        ])->assertStatus(302);
        $this->assertEquals('Completed', $followUp->fresh()->status);
        $this->assertDatabaseHas('emails', ['to_email' => 'info@acmeglobal.com']);

        // Resend Call Summary via dedicated endpoint
        $this->actingAs($this->salesperson)->post("/follow-ups/{$followUp->id}/email-summary", [
            'to_email' => 'info@acmeglobal.com',
            'subject' => 'Call Discussion Summary - Acme Global',
            'notes' => 'Discussed pricing and contract sign-off next week.',
        ])->assertStatus(302);

        // Add Activity
        $this->actingAs($this->salesperson)->post("/leads/{$lead->id}/activities", [
            'type' => 'Send quotation',
            'title' => 'Send draft quotation to CFO',
            'due_date' => date('Y-m-d'),
        ])->assertStatus(302);
        $activity = Activity::where('lead_id', $lead->id)->firstOrFail();
        $this->actingAs($this->salesperson)->post("/activities/{$activity->id}/complete")->assertStatus(302);
        $this->assertEquals('Completed', $activity->fresh()->status);

        // Add Note
        $this->actingAs($this->salesperson)->post("/leads/{$lead->id}/notes", [
            'note' => 'Legal team has signed off on the security compliance clauses.',
        ])->assertStatus(302);
        $this->assertDatabaseHas('lead_notes', ['lead_id' => $lead->id]);

        // Stage Progression update
        $proposalStage = LeadStage::where('name', 'Proposal')->firstOrFail();
        $this->actingAs($this->salesperson)->post("/leads/{$lead->id}/stage", [
            'stage_id' => $proposalStage->id,
        ])->assertStatus(302);
        $this->assertEquals($proposalStage->id, $lead->fresh()->stage_id);

        // Convert to Opportunity
        $this->actingAs($this->salesperson)->post("/leads/{$lead->id}/convert", [
            'name' => 'Acme Global CRM Implementation',
            'expected_revenue' => 8500.00,
            'probability' => 70,
            'expected_closing_date' => now()->addDays(14)->toDateString(),
            'stage_id' => $proposalStage->id,
            'assigned_to' => $this->salesperson->id,
        ])->assertRedirect();

        $opp = Opportunity::where('lead_id', $lead->id)->firstOrFail();
        $this->assertEquals('Acme Global CRM Implementation', $opp->name);
        $this->assertEquals(8500.00, (float)$opp->expected_revenue);
    }

    /**
     * 5. Test Opportunity Pipeline Kanban & Drag-Drop AJAX
     */
    public function test_opportunity_kanban_and_stage_movement(): void
    {
        $opp = Opportunity::firstOrFail();

        // Test Kanban view rendering
        $this->actingAs($this->salesperson)->get('/opportunities?view=kanban')
            ->assertStatus(200)
            ->assertSee('Total Pipeline Value');

        // Test List view rendering
        $this->actingAs($this->salesperson)->get('/opportunities?view=list')
            ->assertStatus(200);

        // Test AJAX stage movement
        $wonStage = LeadStage::where('name', 'Won')->firstOrFail();
        $ajaxResponse = $this->actingAs($this->salesperson)->postJson("/opportunities/{$opp->id}/stage-ajax", [
            'stage_id' => $wonStage->id,
        ]);
        $ajaxResponse->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals('Won', $opp->fresh()->status);
        $this->assertEquals(100, $opp->fresh()->probability);

        // Test Mark Lost
        $this->actingAs($this->salesperson)->post("/opportunities/{$opp->id}/lost", [
            'lost_reason' => 'Client postponed budget to Q4.',
        ])->assertStatus(302);
        $this->assertEquals('Lost', $opp->fresh()->status);
    }

    /**
     * 6. Test Quotation Engine, Calculations, PDF, and Email
     */
    public function test_quotations_engine_and_pdf_generation(): void
    {
        $opp = Opportunity::firstOrFail();
        $service = Service::firstOrFail();

        // Index page
        $this->actingAs($this->salesperson)->get('/quotations')->assertStatus(200);

        // Create Quotation
        $response = $this->actingAs($this->salesperson)->post('/quotations', [
            'customer_name' => 'MegaTech Enterprises',
            'customer_email' => 'billing@megatech.com',
            'customer_phone' => '+1 (555) 123-9999',
            'quotation_date' => date('Y-m-d'),
            'valid_until' => now()->addDays(20)->toDateString(),
            'opportunity_id' => $opp->id,
            'lead_id' => $opp->lead_id,
            'discount_amount' => 50.00,
            'notes' => 'Includes 1 year complimentary updates.',
            'terms_conditions' => 'Standard license agreement.',
            'status' => 'Draft',
            'items' => [
                [
                    'service_id' => $service->id,
                    'description' => 'License Pack',
                    'quantity' => 2,
                    'price' => 1000.00, // 2000
                    'discount' => 0.00,
                    'tax_percentage' => 10.00, // 200 tax
                ],
                [
                    'service_id' => null,
                    'description' => 'Custom API Integration',
                    'quantity' => 1,
                    'price' => 500.00, // 500
                    'discount' => 0.00,
                    'tax_percentage' => 10.00, // 50 tax
                ]
            ]
        ]);

        $quotation = Quotation::where('customer_email', 'billing@megatech.com')->firstOrFail();
        $response->assertRedirect(route('quotations.show', $quotation->id));

        // Subtotal = 2500, Discount = 50, Tax = 250, Total = 2700
        $this->assertEquals(2500.00, (float)$quotation->subtotal);
        $this->assertEquals(50.00, (float)$quotation->discount_amount);
        $this->assertEquals(250.00, (float)$quotation->tax_amount);
        $this->assertEquals(2700.00, (float)$quotation->total_amount);

        // Show page
        $this->actingAs($this->salesperson)->get("/quotations/{$quotation->id}")
            ->assertStatus(200)
            ->assertSee('MegaTech Enterprises');

        // Print page
        $this->actingAs($this->salesperson)->get("/quotations/{$quotation->id}/print")
            ->assertStatus(200)
            ->assertSee($quotation->quotation_number);

        // PDF Generation
        $pdfResponse = $this->actingAs($this->salesperson)->get("/quotations/{$quotation->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));

        // Send Email simulation
        $this->actingAs($this->salesperson)->post("/quotations/{$quotation->id}/email", [
            'to_email' => 'billing@megatech.com',
            'subject' => 'Your Quotation from Apex',
            'message' => 'Please find attached quotation.',
        ])->assertStatus(302);
        $this->assertEquals('Sent', $quotation->fresh()->status);
        $this->assertDatabaseHas('emails', ['to_email' => 'billing@megatech.com']);

        // Mark Accepted
        $this->actingAs($this->salesperson)->post("/quotations/{$quotation->id}/status", [
            'status' => 'Accepted',
        ])->assertStatus(302);
        $this->assertEquals('Accepted', $quotation->fresh()->status);
    }

    /**
     * 7. Test Reports & CSV Export
     */
    public function test_reports_and_export(): void
    {
        $this->actingAs($this->manager)->get('/reports')->assertStatus(200);

        $exportResponse = $this->actingAs($this->manager)->get('/reports/export/leads');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type') ?? '');
    }

    /**
     * 8. Test Notifications System
     */
    public function test_notifications(): void
    {
        $notif = Notification::create([
            'user_id' => $this->telecaller->id,
            'title' => 'Follow-up Due',
            'message' => 'Call scheduled with Dr. Elena',
            'link' => '/leads',
        ]);

        $this->actingAs($this->telecaller)->get('/notifications')->assertStatus(200)->assertSee('Follow-up Due');

        // Mark Read via POST
        $this->actingAs($this->telecaller)->post("/notifications/{$notif->id}/read")->assertRedirect('/leads');
        $this->assertNotNull($notif->fresh()->read_at);

        // Mark Read via GET (when clicking directly from top bell navbar link)
        $notif2 = Notification::create([
            'user_id' => $this->telecaller->id,
            'title' => 'New Inbound Inquiry',
            'message' => 'New lead assigned',
            'link' => '/leads',
        ]);
        $this->actingAs($this->telecaller)->get("/notifications/{$notif2->id}/read")->assertRedirect('/leads');
        $this->assertNotNull($notif2->fresh()->read_at);

        // Mark All Read via POST and GET
        $this->actingAs($this->telecaller)->post('/notifications/read-all')->assertStatus(302);
        $this->actingAs($this->telecaller)->get('/notifications/read-all')->assertStatus(302);
    }

    /**
     * 9. Test Profile Management
     */
    public function test_profile_update_and_password_change(): void
    {
        $this->actingAs($this->salesperson)->get('/profile')->assertStatus(200);

        $this->actingAs($this->salesperson)->post('/profile', [
            'name' => 'David Miller Senior',
            'email' => 'sales@crm.com',
            'phone' => '+1 (555) 999-1111',
        ])->assertStatus(302);
        $this->assertEquals('David Miller Senior', $this->salesperson->fresh()->name);

        $this->actingAs($this->salesperson)->post('/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(302);
    }

    /**
     * 10. Test Pagination & Per-Page Options (Default 10, Options 25, 50, 100)
     */
    public function test_pagination_default_and_per_page_options(): void
    {
        // 1. Check default 10 per page in Leads
        $response = $this->actingAs($this->admin)->get('/leads');
        $response->assertStatus(200);
        $response->assertSee('10 per page');
        $response->assertSee('25 per page');
        $response->assertSee('50 per page');
        $response->assertSee('100 per page');

        // 2. Test switching to 25 per page
        $response25 = $this->actingAs($this->admin)->get('/leads?per_page=25');
        $response25->assertStatus(200);
        $response25->assertSee('25 per page');

        // 3. Test Opportunities list view pagination
        $responseOpps = $this->actingAs($this->admin)->get('/opportunities?view=list&per_page=50');
        $responseOpps->assertStatus(200);
        $responseOpps->assertSee('50 per page');

        // 4. Test Follow-ups pagination
        $responseFu = $this->actingAs($this->admin)->get('/follow-ups?per_page=100');
        $responseFu->assertStatus(200);
        $responseFu->assertSee('100 per page');
    }
}
