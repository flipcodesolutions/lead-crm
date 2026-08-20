<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Role;
use App\Models\User;
use App\Services\LeadImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeadImportController extends Controller
{
    protected LeadImportService $importService;

    public function __construct(LeadImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Check if current user is Admin or Manager.
     */
    protected function authorizeManagerOrAdmin(): void
    {
        $roleName = Auth::user()?->role?->name;
        if (!in_array($roleName, ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized. Only Admin and Manager can import bulk leads.');
        }
    }

    /**
     * Step 1: Show file upload and settings screen.
     */
    public function create()
    {
        $this->authorizeManagerOrAdmin();

        $stages = LeadStage::orderBy('sort_order')->get();
        $statuses = LeadStatus::where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        
        $salesRoleIds = Role::whereIn('name', ['Salesperson', 'Telecaller', 'Manager', 'Admin'])->pluck('id');
        $users = User::whereIn('role_id', $salesRoleIds)->where('status', 1)->get();

        return view('leads.import', compact('stages', 'statuses', 'sources', 'users'));
    }

    /**
     * Step 2: Upload temporary file and show dynamic Column Mapping screen.
     */
    public function preview(Request $request)
    {
        $this->authorizeManagerOrAdmin();

        $request->validate([
            'file' => 'required|file|max:20480|extensions:csv,txt,xlsx,xls', // up to 20MB
            'stage_id' => 'required|exists:lead_stages,id',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assignment_mode' => 'required|in:specific,round_robin,unassigned',
            'assigned_user_id' => 'nullable|exists:users,id',
            'duplicate_action' => 'required|in:skip,update,import',
            'auto_append_unmapped' => 'nullable|boolean',
        ]);

        $uploadedFile = $request->file('file');
        
        // Ensure storage directory exists
        $tempDir = storage_path('app/temp_imports');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = 'import_' . uniqid() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
        $tempPath = $uploadedFile->move($tempDir, $filename)->getPathname();

        try {
            $parsed = $this->importService->parseFile($tempPath, 3);
            $suggestedMapping = $this->importService->suggestMapping($parsed['headers']);
        } catch (\Exception $e) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            return back()->with('error', 'Error reading uploaded file: ' . $e->getMessage());
        }

        $settings = [
            'temp_file_path' => $tempPath,
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'stage_id' => $request->input('stage_id'),
            'status_id' => $request->input('status_id'),
            'source_id' => $request->input('source_id'),
            'assignment_mode' => $request->input('assignment_mode'),
            'assigned_user_id' => $request->input('assigned_user_id'),
            'duplicate_action' => $request->input('duplicate_action'),
            'auto_append_unmapped' => $request->boolean('auto_append_unmapped', true),
        ];

        return view('leads.import_mapping', [
            'headers' => $parsed['headers'],
            'previewRows' => $parsed['preview_rows'],
            'totalRows' => $parsed['total_rows_count'],
            'suggestedMapping' => $suggestedMapping,
            'settings' => $settings,
        ]);
    }

    /**
     * Step 3: Process the bulk lead import.
     */
    public function execute(Request $request)
    {
        $this->authorizeManagerOrAdmin();

        $request->validate([
            'temp_file_path' => 'required|string',
            'mapping' => 'required|array',
            'stage_id' => 'required|exists:lead_stages,id',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assignment_mode' => 'required|in:specific,round_robin,unassigned',
            'assigned_user_id' => 'nullable|exists:users,id',
            'duplicate_action' => 'required|in:skip,update,import',
            'auto_append_unmapped' => 'nullable|boolean',
        ]);

        $filePath = $request->input('temp_file_path');
        if (!file_exists($filePath)) {
            return redirect()->route('leads.import')->with('error', 'Temporary upload session expired. Please upload the file again.');
        }

        $mapping = $request->input('mapping');

        try {
            $stats = $this->importService->processImport(
                $filePath,
                $mapping,
                (int)$request->input('stage_id'),
                $request->filled('status_id') ? (int)$request->input('status_id') : null,
                $request->filled('source_id') ? (int)$request->input('source_id') : null,
                $request->input('assignment_mode'),
                $request->filled('assigned_user_id') ? (int)$request->input('assigned_user_id') : null,
                $request->input('duplicate_action'),
                $request->boolean('auto_append_unmapped', true)
            );

            // Clean up temporary file
            @unlink($filePath);

            $message = "🎉 Bulk Import Completed! {$stats['created']} leads created successfully.";
            if ($stats['updated'] > 0) {
                $message .= " {$stats['updated']} existing leads updated.";
            }
            if ($stats['skipped'] > 0) {
                $message .= " {$stats['skipped']} duplicate/empty rows skipped.";
            }

            return redirect()->route('leads.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Import execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Sample CSV Template.
     */
    public function sampleCsv()
    {
        $this->authorizeManagerOrAdmin();

        $content = $this->importService->generateSampleCsv();

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="lead_import_sample_template.csv"',
        ]);
    }
}
