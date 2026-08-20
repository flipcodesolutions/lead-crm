<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveType::withCount(['allocations', 'requests']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%");
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $leaveTypes = $query->latest()->paginate($perPage)->withQueryString();

        return view('hr.leave_types.index', compact('leaveTypes', 'perPage'));
    }

    public function create()
    {
        return view('hr.leave_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:leave_types,name',
            'total_days' => 'required|integer|min:0|max:365',
            'is_paid' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);

        LeaveType::create($validated);

        return redirect()->route('hr.leave-types.index')->with('success', 'Leave Type created successfully.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('hr.leave_types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:leave_types,name,' . $leaveType->id,
            'total_days' => 'required|integer|min:0|max:365',
            'is_paid' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);

        $leaveType->update($validated);

        return redirect()->route('hr.leave-types.index')->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->requests()->count() > 0) {
            return back()->with('error', 'Cannot delete leave type with existing employee leave requests.');
        }

        $leaveType->delete();

        return redirect()->route('hr.leave-types.index')->with('success', 'Leave Type deleted successfully.');
    }
}
