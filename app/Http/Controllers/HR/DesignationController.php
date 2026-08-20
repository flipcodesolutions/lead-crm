<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $query = Designation::withCount('employees');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $designations = $query->latest()->paginate($perPage)->withQueryString();

        return view('hr.designations.index', compact('designations', 'perPage'));
    }

    public function create()
    {
        return view('hr.designations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:designations,name',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);

        Designation::create($validated);

        return redirect()->route('hr.designations.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        return view('hr.designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:designations,name,' . $designation->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);

        $designation->update($validated);

        return redirect()->route('hr.designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        if ($designation->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete designation assigned to active employees.');
        }

        $designation->delete();

        return redirect()->route('hr.designations.index')->with('success', 'Designation deleted successfully.');
    }
}
