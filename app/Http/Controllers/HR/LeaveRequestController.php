<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['employee.department', 'leaveType', 'approver']);

        // Scope by role: regular employees only see their own leaves or their direct subordinates
        if (!$user->canManageHR()) {
            $employee = $user->employee;
            if ($employee) {
                $subordinateIds = $employee->subordinates()->pluck('id')->toArray();
                $allowedIds = array_merge([$employee->id], $subordinateIds);
                $query->whereIn('employee_id', $allowedIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
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

        $leaveRequests = $query->latest()->paginate($perPage)->withQueryString();

        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('status', 1)->get();

        return view('hr.leave_requests.index', compact('leaveRequests', 'employees', 'leaveTypes', 'perPage'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $currentYear = Carbon::now()->year;
        
        $currentEmployee = $user->employee;
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('status', 1)->get();

        $allocations = [];
        if ($currentEmployee) {
            $allocations = LeaveAllocation::with('leaveType')
                ->where('employee_id', $currentEmployee->id)
                ->where('year', $currentYear)
                ->get();
        }

        return view('hr.leave_requests.create', compact('employees', 'leaveTypes', 'currentEmployee', 'allocations', 'currentYear'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
        ]);

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = Carbon::parse($validated['to_date']);
        $totalDays = $fromDate->diffInDays($toDate) + 1;
        $year = $fromDate->year;

        // 1. Check for overlapping approved leave requests
        $overlap = LeaveRequest::where('employee_id', $validated['employee_id'])
            ->where('status', 'Approved')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereDate('from_date', '<=', $toDate->toDateString())
                  ->whereDate('to_date', '>=', $fromDate->toDateString());
            })
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'An approved leave request already exists for this employee covering the selected date range.');
        }

        // 2. Check allocation quota (if paid/allocated leave)
        $allocation = LeaveAllocation::where('employee_id', $validated['employee_id'])
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $year)
            ->first();

        $leaveType = LeaveType::find($validated['leave_type_id']);
        if ($leaveType && $leaveType->is_paid && $allocation) {
            if ($allocation->remaining_days < $totalDays) {
                return back()->withInput()->with('error', "Insufficient leave balance. You have {$allocation->remaining_days} remaining days for {$leaveType->name}, but requested {$totalDays} days.");
            }
        }

        LeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return redirect()->route('hr.leave-requests.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee.department', 'employee.designation', 'employee.manager', 'leaveType', 'approver']);
        return view('hr.leave_requests.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        if (!$user->canManageHR()) {
            abort(403, 'Unauthorized to approve leave requests.');
        }

        if ($leaveRequest->status === 'Approved') {
            return back()->with('error', 'This leave request is already approved.');
        }

        DB::beginTransaction();
        try {
            $year = Carbon::parse($leaveRequest->from_date)->year;

            // Deduct from allocation
            $allocation = LeaveAllocation::firstOrNew([
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => $year,
            ]);

            $allocation->used_days = ($allocation->used_days ?? 0) + $leaveRequest->total_days;
            $allocation->allocated_days = $allocation->allocated_days ?? $leaveRequest->leaveType->total_days;
            $allocation->remaining_days = max(0, $allocation->allocated_days - $allocation->used_days);
            $allocation->save();

            $leaveRequest->update([
                'status' => 'Approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('hr.leave-requests.index')->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve leave: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        if (!$user->canManageHR()) {
            abort(403, 'Unauthorized to reject leave requests.');
        }

        $request->validate([
            'rejected_reason' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'Rejected',
            'rejected_reason' => $request->rejected_reason,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('hr.leave-requests.index')->with('success', 'Leave request rejected.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // If previously approved, refund used days back to remaining balance
        if ($leaveRequest->status === 'Approved') {
            $year = Carbon::parse($leaveRequest->from_date)->year;
            $allocation = LeaveAllocation::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $year)
                ->first();

            if ($allocation) {
                $allocation->used_days = max(0, $allocation->used_days - $leaveRequest->total_days);
                $allocation->remaining_days = max(0, $allocation->allocated_days - $allocation->used_days);
                $allocation->save();
            }
        }

        $leaveRequest->update(['status' => 'Cancelled']);

        return redirect()->route('hr.leave-requests.index')->with('success', 'Leave request cancelled.');
    }
}
