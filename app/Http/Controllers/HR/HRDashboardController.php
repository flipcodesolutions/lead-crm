<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\TaxSlab;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Headcount Metrics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'Active')->count();
        $inactiveEmployees = Employee::where('status', 'Inactive')->count();
        $resignedEmployees = Employee::where('status', 'Resigned')->count();

        // Attendance & Leave Metrics
        $employeesOnLeaveToday = LeaveRequest::with('employee.department')
            ->where('status', 'Approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->get();

        $pendingLeaveRequests = LeaveRequest::with(['employee.department', 'leaveType'])
            ->where('status', 'Pending')
            ->latest()
            ->take(6)
            ->get();

        // Department-wise Headcount Breakdown
        $departments = Department::withCount(['employees' => function ($q) {
            $q->where('status', 'Active');
        }])->get();

        // Current Month Payroll Snapshot
        $monthlySalaries = EmployeeSalary::where('status', 1)->get();
        $totalMonthlyPayroll = $monthlySalaries->sum('gross_salary');
        $totalAnnualPayroll = $monthlySalaries->sum('annual_salary');
        $totalMonthlyPfDeduction = $monthlySalaries->sum('pf_deduction');
        
        $currentMonthPayrollCount = Payroll::where('month', $currentMonth)->where('year', $currentYear)->count();
        $currentMonthPaidPayrollSum = Payroll::where('month', $currentMonth)->where('year', $currentYear)->sum('net_salary');
        $currentMonthTaxDeduction = Payroll::where('month', $currentMonth)->where('year', $currentYear)->sum('tax');

        // Recent Payrolls
        $recentPayrolls = Payroll::with(['employee.department', 'employee.designation'])
            ->latest()
            ->take(6)
            ->get();

        return view('hr.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'inactiveEmployees',
            'resignedEmployees',
            'employeesOnLeaveToday',
            'pendingLeaveRequests',
            'departments',
            'totalMonthlyPayroll',
            'totalAnnualPayroll',
            'totalMonthlyPfDeduction',
            'currentMonthPayrollCount',
            'currentMonthPaidPayrollSum',
            'currentMonthTaxDeduction',
            'recentPayrolls'
        ));
    }
}
