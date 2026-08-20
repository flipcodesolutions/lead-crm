<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        // 1. Detailed Filtered Query
        $query = LeaveRequest::with(['employee.department', 'employee.manager', 'leaveType', 'approver'])
            ->whereYear('from_date', $selectedYear);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('manager_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('manager_id', $request->manager_id);
            });
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('from_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('to_date', '<=', $request->date_to);
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $leaveRecords = $query->latest('from_date')->paginate($perPage)->withQueryString();

        // 2. Employee-wise Summary
        $employeeSummaryQuery = Employee::with(['department', 'designation', 'leaveAllocations' => function ($q) use ($selectedYear) {
            $q->where('year', $selectedYear);
        }])->where('status', 'Active');

        if ($request->filled('department_id')) {
            $employeeSummaryQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('employee_id')) {
            $employeeSummaryQuery->where('id', $request->employee_id);
        }

        $employeeSummaries = $employeeSummaryQuery->get()->map(function ($emp) {
            $totalAllocated = $emp->leaveAllocations->sum('allocated_days');
            $totalUsed = $emp->leaveAllocations->sum('used_days');
            $totalRemaining = $emp->leaveAllocations->sum('remaining_days');

            return (object) [
                'employee' => $emp,
                'total_allocated' => $totalAllocated,
                'total_used' => $totalUsed,
                'total_remaining' => $totalRemaining,
            ];
        });

        $departments = Department::where('status', 1)->get();
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $managers = Employee::where('status', 'Active')->has('subordinates')->get();
        $leaveTypes = LeaveType::where('status', 1)->get();
        $years = range(Carbon::now()->year - 3, Carbon::now()->year + 1);

        return view('hr.leave_reports.index', compact(
            'leaveRecords',
            'employeeSummaries',
            'departments',
            'employees',
            'managers',
            'leaveTypes',
            'selectedYear',
            'years',
            'perPage'
        ));
    }
}
