<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeSalary::with(['employee.department', 'employee.designation'])
            ->where('status', 1);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('employee', function ($q) use ($s) {
                $q->where('first_name', 'LIKE', "%{$s}%")
                  ->orWhere('last_name', 'LIKE', "%{$s}%")
                  ->orWhere('employee_code', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $salaries = $query->latest('effective_from')->paginate($perPage)->withQueryString();

        return view('hr.salaries.index', compact('salaries', 'perPage'));
    }

    public function create(Request $request)
    {
        $selectedEmployeeId = $request->get('employee_id');
        $employees = Employee::where('status', 'Active')->orderBy('first_name')->get();
        $selectedEmployee = $selectedEmployeeId ? Employee::with('currentSalary')->find($selectedEmployeeId) : null;

        return view('hr.salaries.create', compact('employees', 'selectedEmployee', 'selectedEmployeeId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'other_earnings' => 'nullable|numeric|min:0',
            'pf_deduction' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $basic = (float) $validated['basic_salary'];
        $hra = (float) $validated['hra'];
        $allowances = (float) ($validated['allowances'] ?? 0);
        $otherEarnings = (float) ($validated['other_earnings'] ?? 0);
        $pfDeduction = (float) ($validated['pf_deduction'] ?? 0);
        $otherDeduction = (float) ($validated['other_deduction'] ?? 0);

        $gross = $basic + $hra + $allowances + $otherEarnings;
        $annual = $gross * 12;

        $effectiveFrom = Carbon::parse($validated['effective_from']);

        // Maintain Salary History: deactivate old active salary and set effective_to
        $oldSalary = EmployeeSalary::where('employee_id', $validated['employee_id'])
            ->where('status', 1)
            ->first();

        if ($oldSalary) {
            $oldSalary->update([
                'status' => 0,
                'effective_to' => (clone $effectiveFrom)->subDay()->toDateString(),
            ]);
        }

        // Create new active salary revision
        EmployeeSalary::create([
            'employee_id' => $validated['employee_id'],
            'basic_salary' => $basic,
            'hra' => $hra,
            'allowances' => $allowances,
            'other_earnings' => $otherEarnings,
            'gross_salary' => $gross,
            'pf_deduction' => $pfDeduction,
            'other_deduction' => $otherDeduction,
            'annual_salary' => $annual,
            'effective_from' => $effectiveFrom->toDateString(),
            'effective_to' => null,
            'status' => 1,
        ]);

        return redirect()->route('hr.employees.show', $validated['employee_id'])
            ->with('success', 'Salary structure updated successfully. History preserved.');
    }

    public function history(Employee $employee)
    {
        $salaries = EmployeeSalary::where('employee_id', $employee->id)
            ->orderBy('effective_from', 'desc')
            ->get();

        return view('hr.salaries.history', compact('employee', 'salaries'));
    }
}
