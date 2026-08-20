<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use Illuminate\Http\Request;

class LeadStageController extends Controller
{
    public function index()
    {
        $stages = LeadStage::withCount(['leads', 'opportunities'])->orderBy('sort_order')->get();
        return view('admin.lead_stages.index', compact('stages'));
    }

    public function create()
    {
        return view('admin.lead_stages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:lead_stages,name',
            'sort_order' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        LeadStage::create($validated);

        return redirect()->route('admin.lead-stages.index')->with('success', 'Pipeline stage created.');
    }

    public function edit(LeadStage $leadStage)
    {
        return view('admin.lead_stages.edit', compact('leadStage'));
    }

    public function update(Request $request, LeadStage $leadStage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:lead_stages,name,' . $leadStage->id,
            'sort_order' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        $leadStage->update($validated);

        return redirect()->route('admin.lead-stages.index')->with('success', 'Pipeline stage updated.');
    }

    public function destroy(LeadStage $leadStage)
    {
        if ($leadStage->leads()->count() > 0 || $leadStage->opportunities()->count() > 0) {
            return back()->with('error', 'Cannot delete stage currently in use by leads or opportunities.');
        }

        $leadStage->delete();
        return redirect()->route('admin.lead-stages.index')->with('success', 'Pipeline stage deleted.');
    }
}
