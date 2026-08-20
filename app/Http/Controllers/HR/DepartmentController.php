<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount('employees');

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

        $departments = $query->latest()->paginate($perPage)->withQueryString();

        return view('hr.departments.index', compact('departments', 'perPage'));
    }

    public function create()
    {
        return view('hr.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:departments,name',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);

        Department::create($validated);

        return redirect()->route('hr.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('hr.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);

        $department->update($validated);

        return redirect()->route('hr.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete department with active employees assigned.');
        }

        $department->delete();

        return redirect()->route('hr.departments.index')->with('success', 'Department deleted successfully.');
    }
}
