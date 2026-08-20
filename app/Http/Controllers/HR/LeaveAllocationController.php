<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveAllocationController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        $query = LeaveAllocation::with(['employee.department', 'leaveType'])
            ->where('year', $selectedYear);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('employee', function ($q) use ($s) {
                $q->where('first_name', 'LIKE', "%{$s}%")
                  ->orWhere('last_name', 'LIKE', "%{$s}%")
                  ->orWhere('employee_code', 'LIKE', "%{$s}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $allocations = $query->paginate($perPage)->withQueryString();

        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('status', 1)->get();
        $years = range(Carbon::now()->year - 2, Carbon::now()->year + 2);

        return view('hr.leave_allocations.index', compact('allocations', 'employees', 'leaveTypes', 'selectedYear', 'years', 'perPage'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('status', 1)->get();
        $currentYear = Carbon::now()->year;
        $selectedEmployeeId = $request->get('employee_id');

        return view('hr.leave_allocations.create', compact('employees', 'leaveTypes', 'currentYear', 'selectedEmployeeId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required', // can be 'all' or specific employee_id
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2020|max:2050',
            'allocated_days' => 'required|numeric|min:0|max:365',
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        $allocatedDays = (float) $validated['allocated_days'];
        $year = (int) $validated['year'];

        if ($validated['employee_id'] === 'all') {
            $activeEmployees = Employee::where('status', 'Active')->get();
            foreach ($activeEmployees as $emp) {
                $allocation = LeaveAllocation::firstOrNew([
                    'employee_id' => $emp->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ]);

                $allocation->allocated_days = $allocatedDays;
                $used = (float) ($allocation->used_days ?? 0);
                $allocation->remaining_days = max(0, $allocatedDays - $used);
                $allocation->save();
            }

            return redirect()->route('hr.leave-allocations.index', ['year' => $year])
                ->with('success', "Allocated {$allocatedDays} days of {$leaveType->name} to all active employees for year {$year}.");
        } else {
            $allocation = LeaveAllocation::firstOrNew([
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ]);

            $allocation->allocated_days = $allocatedDays;
            $used = (float) ($allocation->used_days ?? 0);
            $allocation->remaining_days = max(0, $allocatedDays - $used);
            $allocation->save();

            return redirect()->route('hr.leave-allocations.index', ['year' => $year])
                ->with('success', 'Leave allocation saved successfully.');
        }
    }
}
