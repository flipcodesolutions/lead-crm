<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\User;
use App\Services\LeadService;
use App\Services\OpportunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Lead::with(['source', 'leadStatus', 'stage', 'assignedUser', 'creator']);

        // Scope by role if telecaller or salesperson
        if ($user->isTelecaller() || $user->isSalesperson()) {
            $query->where('assigned_to', $user->id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('lead_number', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $leads = $query->latest()->paginate($perPage)->withQueryString();

        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->get();
        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();

        return view('leads.index', compact('leads', 'sources', 'statuses', 'stages', 'users', 'perPage'));
    }

    public function create()
    {
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->get();
        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();

        return view('leads.create', compact('sources', 'statuses', 'stages', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'alternate_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'source_id' => 'nullable|exists:lead_sources,id',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'stage_id' => 'nullable|exists:lead_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $lead = $this->leadService->createLead($validated, Auth::id());

        return redirect()->route('leads.show', $lead->id)
            ->with('success', "Lead #{$lead->lead_number} created successfully.");
    }

    public function show(Lead $lead)
    {
        // Authorization check for telecaller/salesperson
        $user = Auth::user();
        if (($user->isTelecaller() || $user->isSalesperson()) && $lead->assigned_to !== $user->id) {
            abort(403, 'You are not authorized to view this lead.');
        }

        $lead->load([
            'source',
            'leadStatus',
            'stage',
            'assignedUser',
            'creator',
            'assignments.assigner',
            'assignments.assignee',
            'followUps.user',
            'activities.user',
            'notes.user',
            'opportunity.stage',
            'quotations',
            'emails.user',
        ]);

        $allStages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->get();

        return view('leads.show', compact('lead', 'allStages', 'users', 'sources', 'statuses'));
    }

    public function edit(Lead $lead)
    {
        $sources = LeadSource::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->get();
        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();

        return view('leads.edit', compact('lead', 'sources', 'statuses', 'stages', 'users'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'alternate_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'source_id' => 'nullable|exists:lead_sources,id',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'stage_id' => 'nullable|exists:lead_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'assignment_remarks' => 'nullable|string',
        ]);

        $this->leadService->updateLead($lead, $validated);

        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead details updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403, 'Unauthorized to delete leads.');
        }

        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function assign(Request $request, Lead $lead)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $this->leadService->assignLead($lead, $request->assigned_to, Auth::id(), $request->remarks);

        return back()->with('success', 'Lead successfully assigned.');
    }

    public function addFollowUp(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Call,Meeting,Email,WhatsApp,Other',
            'follow_up_date' => 'required|date',
            'follow_up_time' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->leadService->addFollowUp($lead, $validated, Auth::id());

        return back()->with('success', 'Follow-up scheduled successfully.');
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $this->leadService->addActivity($lead, $validated, Auth::id());

        return back()->with('success', 'Activity added successfully.');
    }

    public function addNote(Request $request, Lead $lead)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $this->leadService->addNote($lead, $request->note, Auth::id());

        return back()->with('success', 'Note added successfully.');
    }

    public function qualify(Request $request, Lead $lead)
    {
        $this->leadService->qualifyLead($lead, $request->stage_id);

        return back()->with('success', 'Lead has been marked as Qualified.');
    }

    public function convertToOpportunity(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|integer|between:0,100',
            'expected_closing_date' => 'nullable|date',
            'stage_id' => 'nullable|exists:lead_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $opportunity = $this->leadService->convertToOpportunity($lead, $validated);

        return redirect()->route('opportunities.show', $opportunity->id)
            ->with('success', "Lead converted to Opportunity #{$opportunity->opportunity_number}.");
    }

    public function markLost(Request $request, Lead $lead)
    {
        $request->validate([
            'lost_reason' => 'required|string|max:500',
        ]);

        $this->leadService->markLost($lead, $request->lost_reason);

        return back()->with('info', 'Lead marked as Lost/Disqualified.');
    }

    public function updateStage(Request $request, Lead $lead)
    {
        $request->validate([
            'stage_id' => 'required|exists:lead_stages,id',
        ]);

        $lead->update(['stage_id' => $request->stage_id]);

        return back()->with('success', 'Lead stage updated.');
    }
}
