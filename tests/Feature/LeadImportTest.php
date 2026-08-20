<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $telecaller;
    protected User $salesperson;
    protected LeadStage $stageNew;
    protected LeadStatus $statusNew;
    protected LeadSource $sourceWeb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::where('email', 'admin@crm.com')->first();
        $this->manager = User::where('email', 'manager@crm.com')->first();
        $this->telecaller = User::where('email', 'telecaller@crm.com')->first();
        $this->salesperson = User::where('email', 'sales@crm.com')->first();

        $this->stageNew = LeadStage::where('name', 'New')->first();
        $this->statusNew = LeadStatus::where('name', 'New')->first();
        $this->sourceWeb = LeadSource::first();
    }

    /**
     * 1. Test Role Permissions
     */
    public function test_only_admin_and_manager_can_access_import(): void
    {
        // Telecaller and Sales rep should be blocked (403)
        $this->actingAs($this->telecaller)->get('/leads/import')->assertStatus(403);
        $this->actingAs($this->salesperson)->get('/leads/import')->assertStatus(403);

        // Admin and Manager should have access (200)
        $this->actingAs($this->admin)->get('/leads/import')->assertStatus(200)->assertSee('Bulk Lead Import');
        $this->actingAs($this->manager)->get('/leads/import')->assertStatus(200)->assertSee('Bulk Lead Import');
    }

    /**
     * 2. Test Preview & Column Mapping Suggestions
     */
    public function test_upload_and_preview_mapping(): void
    {
        $csvContent = "created_time,campaign_name,platform,what_best_describes_you_?,approximate_area_required,when_are_you_planning,full_name,phone,city,state,inbox_url\n" .
                      "7-9-26,Argil- lead-june26,ig,homeowner,500 - 1000 sqft,within 30 days,Rajiv Pukhrambam,9.18787E+11,Moirang,Manipur,https://business.facebook.com/leads/101\n" .
                      "7-9-26,Argil- lead-june26,fb,homeowner,500 - 1000 sqft,within 7 days,Jayshree Shah,+91 98250 12345,Ahmedabad,Gujarat,https://business.facebook.com/leads/102\n";

        $file = UploadedFile::fake()->createWithContent('meta_leads.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/leads/import/preview', [
            'file' => $file,
            'stage_id' => $this->stageNew->id,
            'status_id' => $this->statusNew->id,
            'source_id' => $this->sourceWeb->id,
            'assignment_mode' => 'round_robin',
            'duplicate_action' => 'skip',
            'auto_append_unmapped' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Column Mapping Wizard');
        $response->assertSee('Rajiv Pukhrambam');
        $response->assertSee('Jayshree Shah');
        $response->assertSee('full_name');
        $response->assertSee('phone');
    }

    /**
     * 4. Test Full Execution with Meta Ads Columns & Scientific Phone Parsing
     */
    public function test_execute_bulk_import_with_meta_ads_data(): void
    {
        $tempDir = storage_path('app/temp_imports');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/test_import_' . time() . '.csv';
        $csvContent = "created_time,campaign_name,platform,what_best_describes_you_?,approximate_area_required,when_are_you_planning,full_name,phone,city,state,inbox_url\n" .
                      "7-9-26,Argil- lead-june26,ig,homeowner,500 - 1000 sqft,within 30 days,Rajiv Pukhrambam,9.18787E+11,Moirang,Manipur,https://business.facebook.com/leads/101\n" .
                      "7-9-26,Argil- lead-june26,fb,homeowner,500 - 1000 sqft,within 7 days,Jayshree Shah,9825099999,Ahmedabad,Gujarat,https://business.facebook.com/leads/102\n";

        file_put_contents($tempPath, $csvContent);

        $mapping = [
            'created_time' => 'append_to_notes',
            'campaign_name' => 'append_to_notes',
            'platform' => 'source',
            'what_best_describes_you_?' => 'append_to_notes',
            'approximate_area_required' => 'append_to_notes',
            'when_are_you_planning' => 'append_to_notes',
            'full_name' => 'name',
            'phone' => 'phone',
            'city' => 'city',
            'state' => 'state',
            'inbox_url' => 'append_to_notes',
        ];

        $response = $this->actingAs($this->admin)->post('/leads/import/execute', [
            'temp_file_path' => $tempPath,
            'mapping' => $mapping,
            'stage_id' => $this->stageNew->id,
            'status_id' => $this->statusNew->id,
            'source_id' => $this->sourceWeb->id,
            'assignment_mode' => 'round_robin',
            'duplicate_action' => 'skip',
            'auto_append_unmapped' => 1,
        ]);

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        // Check Lead 1: Rajiv Pukhrambam
        $lead1 = Lead::where('name', 'Rajiv Pukhrambam')->first();
        $this->assertNotNull($lead1);
        $this->assertEquals('Moirang', $lead1->city);
        $this->assertEquals('Manipur', $lead1->state);
        // Verify scientific phone 9.18787E+11 was converted into standard digits
        $this->assertStringContainsString('8787', $lead1->phone);
        // Verify campaign questions in description
        $this->assertStringContainsString('homeowner', $lead1->description);
        $this->assertStringContainsString('500 - 1000 sqft', $lead1->description);
        $this->assertStringContainsString('within 30 days', $lead1->description);

        // Check Lead 2: Jayshree Shah
        $lead2 = Lead::where('name', 'Jayshree Shah')->first();
        $this->assertNotNull($lead2);
        $this->assertEquals('Ahmedabad', $lead2->city);
        $this->assertEquals('Gujarat', $lead2->state);
        $this->assertStringContainsString('98250 99999', $lead2->phone);
        $this->assertNotNull($lead2->assigned_to);
    }

    /**
     * 5. Test Duplicate Skip vs Update
     */
    public function test_duplicate_skip_strategy(): void
    {
        $existing = Lead::create([
            'lead_number' => 'LD-TEST-DUP-01',
            'name' => 'Existing Customer',
            'phone' => '+91 99999 88888',
            'city' => 'Delhi',
            'stage_id' => $this->stageNew->id,
            'status_id' => $this->statusNew->id,
            'created_by' => $this->admin->id,
            'status' => 1,
        ]);

        $tempDir = storage_path('app/temp_imports');
        $tempPath = $tempDir . '/test_dup_' . time() . '.csv';
        $csvContent = "full_name,phone,city\n" .
                      "Duplicate Person,+91 99999 88888,Mumbai\n";

        file_put_contents($tempPath, $csvContent);

        $mapping = [
            'full_name' => 'name',
            'phone' => 'phone',
            'city' => 'city',
        ];

        // Run with duplicate_action = 'skip'
        $this->actingAs($this->admin)->post('/leads/import/execute', [
            'temp_file_path' => $tempPath,
            'mapping' => $mapping,
            'stage_id' => $this->stageNew->id,
            'assignment_mode' => 'unassigned',
            'duplicate_action' => 'skip',
            'auto_append_unmapped' => 1,
        ]);

        // Name should remain 'Existing Customer' and not 'Duplicate Person'
        $this->assertEquals('Existing Customer', $existing->fresh()->name);
        $this->assertEquals(1, Lead::where('phone', 'LIKE', '%99999 88888%')->count());
    }

    /**
     * 6. Test Excel .xlsx Format Import
     */
    public function test_xlsx_excel_file_import(): void
    {
        $tempDir = storage_path('app/temp_imports');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $xlsxPath = $tempDir . '/test_excel_' . time() . '.xlsx';

        // Build valid minimal XLSX file using ZipArchive
        $zip = new \ZipArchive();
        $zip->open($xlsxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedString+xml"/>
</Types>';

        $sharedStrings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="6" uniqueCount="6">
    <si><t>Full Name</t></si>
    <si><t>Phone Number</t></si>
    <si><t>City</t></si>
    <si><t>Kunal Kapoor</t></si>
    <si><t>+91 98251 77777</t></si>
    <si><t>Ahmedabad</t></si>
</sst>';

        $sheet1 = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>
        <row r="1">
            <c r="A1" t="s"><v>0</v></c>
            <c r="B1" t="s"><v>1</v></c>
            <c r="C1" t="s"><v>2</v></c>
        </row>
        <row r="2">
            <c r="A2" t="s"><v>3</v></c>
            <c r="B2" t="s"><v>4</v></c>
            <c r="C2" t="s"><v>5</v></c>
        </row>
    </sheetData>
</worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('xl/sharedStrings.xml', $sharedStrings);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);
        $zip->close();

        $mapping = [
            'Full Name' => 'name',
            'Phone Number' => 'phone',
            'City' => 'city',
        ];

        $response = $this->actingAs($this->admin)->post('/leads/import/execute', [
            'temp_file_path' => $xlsxPath,
            'mapping' => $mapping,
            'stage_id' => $this->stageNew->id,
            'assignment_mode' => 'unassigned',
            'duplicate_action' => 'skip',
            'auto_append_unmapped' => 1,
        ]);

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        $lead = Lead::where('name', 'Kunal Kapoor')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Ahmedabad', $lead->city);
        $this->assertEquals('+91 98251 77777', $lead->phone);
    }
}
