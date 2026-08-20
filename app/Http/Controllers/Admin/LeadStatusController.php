<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadStatus;
use Illuminate\Http\Request;

class LeadStatusController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $statuses = LeadStatus::withCount('leads')->latest()->paginate($perPage)->withQueryString();
        return view('admin.lead_statuses.index', compact('statuses', 'perPage'));
    }

    public function create()
    {
        return view('admin.lead_statuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:lead_statuses,name',
            'status' => 'required|in:0,1',
        ]);

        LeadStatus::create($validated);

        return redirect()->route('admin.lead-statuses.index')->with('success', 'Lead status created.');
    }

    public function edit(LeadStatus $leadStatus)
    {
        return view('admin.lead_statuses.edit', compact('leadStatus'));
    }

    public function update(Request $request, LeadStatus $leadStatus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:lead_statuses,name,' . $leadStatus->id,
            'status' => 'required|in:0,1',
        ]);

        $leadStatus->update($validated);

        return redirect()->route('admin.lead-statuses.index')->with('success', 'Lead status updated.');
    }

    public function destroy(LeadStatus $leadStatus)
    {
        if ($leadStatus->leads()->count() > 0) {
            return back()->with('error', 'Cannot delete status with attached leads.');
        }

        $leadStatus->delete();
        return redirect()->route('admin.lead-statuses.index')->with('success', 'Lead status deleted.');
    }
}
