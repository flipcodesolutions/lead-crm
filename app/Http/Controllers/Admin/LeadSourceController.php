<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $sources = LeadSource::withCount('leads')->latest()->paginate($perPage)->withQueryString();
        return view('admin.lead_sources.index', compact('sources', 'perPage'));
    }

    public function create()
    {
        return view('admin.lead_sources.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:lead_sources,name',
            'status' => 'required|in:0,1',
        ]);

        LeadSource::create($validated);

        return redirect()->route('admin.lead-sources.index')->with('success', 'Lead source created.');
    }

    public function edit(LeadSource $leadSource)
    {
        return view('admin.lead_sources.edit', compact('leadSource'));
    }

    public function update(Request $request, LeadSource $leadSource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:lead_sources,name,' . $leadSource->id,
            'status' => 'required|in:0,1',
        ]);

        $leadSource->update($validated);

        return redirect()->route('admin.lead-sources.index')->with('success', 'Lead source updated.');
    }

    public function destroy(LeadSource $leadSource)
    {
        if ($leadSource->leads()->count() > 0) {
            return back()->with('error', 'Cannot delete source with attached leads. Consider setting status to Inactive instead.');
        }

        $leadSource->delete();
        return redirect()->route('admin.lead-sources.index')->with('success', 'Lead source deleted.');
    }
}
