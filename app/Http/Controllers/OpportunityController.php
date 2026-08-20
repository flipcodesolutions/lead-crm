<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\OpportunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpportunityController extends Controller
{
    protected OpportunityService $opportunityService;

    public function __construct(OpportunityService $opportunityService)
    {
        $this->opportunityService = $opportunityService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $viewMode = $request->get('view', 'kanban'); // 'kanban' or 'list'

        $query = Opportunity::with(['lead', 'stage', 'assignedUser', 'quotations']);

        if ($user->isSalesperson()) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('opportunity_number', 'LIKE', "%{$s}%")
                  ->orWhere('customer_name', 'LIKE', "%{$s}%")
                  ->orWhere('company_name', 'LIKE', "%{$s}%");
            });
        }

        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        if ($viewMode === 'list') {
            $opportunities = (clone $query)->latest()->paginate($perPage)->withQueryString();
            return view('opportunities.index', compact('opportunities', 'stages', 'users', 'viewMode', 'perPage'));
        }

        // Kanban View: group opportunities by stage
        $allOpportunities = $query->latest()->get();
        $kanbanData = [];
        $totalPipelineValue = 0;
        $totalOpportunitiesCount = $allOpportunities->count();

        foreach ($stages as $stage) {
            $stageOpps = $allOpportunities->where('stage_id', $stage->id)->values();
            $stageTotalValue = $stageOpps->sum('expected_revenue');
            $totalPipelineValue += $stageTotalValue;

            $kanbanData[] = [
                'stage' => $stage,
                'opportunities' => $stageOpps,
                'count' => $stageOpps->count(),
                'total_value' => $stageTotalValue,
            ];
        }

        return view('opportunities.index', compact(
            'kanbanData',
            'stages',
            'users',
            'viewMode',
            'totalPipelineValue',
            'totalOpportunitiesCount'
        ));
    }

    public function create(Request $request)
    {
        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();
        $lead = null;

        if ($request->filled('lead_id')) {
            $lead = Lead::find($request->lead_id);
        }

        return view('opportunities.create', compact('stages', 'users', 'lead'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'expected_revenue' => 'required|numeric|min:0',
            'probability' => 'required|integer|between:0,100',
            'expected_closing_date' => 'nullable|date',
            'stage_id' => 'required|exists:lead_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $opportunity = $this->opportunityService->createOpportunity($validated);

        return redirect()->route('opportunities.show', $opportunity->id)
            ->with('success', "Opportunity #{$opportunity->opportunity_number} created successfully.");
    }

    public function show(Opportunity $opportunity)
    {
        $opportunity->load(['lead.source', 'lead.status', 'stage', 'assignedUser', 'quotations.items']);
        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();

        return view('opportunities.show', compact('opportunity', 'stages', 'users'));
    }

    public function edit(Opportunity $opportunity)
    {
        $stages = LeadStage::where('status', 1)->orderBy('sort_order')->get();
        $users = User::where('status', 1)->get();

        return view('opportunities.edit', compact('opportunity', 'stages', 'users'));
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'expected_revenue' => 'required|numeric|min:0',
            'probability' => 'required|integer|between:0,100',
            'expected_closing_date' => 'nullable|date',
            'stage_id' => 'required|exists:lead_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'status' => 'required|in:Open,Won,Lost',
        ]);

        $this->opportunityService->updateOpportunity($opportunity, $validated);

        return redirect()->route('opportunities.show', $opportunity->id)
            ->with('success', 'Opportunity updated successfully.');
    }

    public function destroy(Opportunity $opportunity)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403, 'Unauthorized to delete opportunities.');
        }

        $opportunity->delete();
        return redirect()->route('opportunities.index')->with('success', 'Opportunity deleted.');
    }

    /**
     * AJAX endpoint for Drag & Drop Stage movement in Kanban Board
     */
    public function updateStageAjax(Request $request, Opportunity $opportunity)
    {
        $request->validate([
            'stage_id' => 'required|exists:lead_stages,id',
        ]);

        $this->opportunityService->updateStage($opportunity, $request->stage_id);

        return response()->json([
            'success' => true,
            'message' => 'Stage updated successfully.',
            'opportunity' => $opportunity->fresh(['stage']),
        ]);
    }

    public function markWon(Opportunity $opportunity)
    {
        $this->opportunityService->markWon($opportunity);
        return back()->with('success', "Deal marked as WON!");
    }

    public function markLost(Request $request, Opportunity $opportunity)
    {
        $request->validate([
            'lost_reason' => 'required|string|max:500',
        ]);

        $this->opportunityService->markLost($opportunity, $request->lost_reason);
        return back()->with('info', 'Opportunity marked as Lost.');
    }
}
