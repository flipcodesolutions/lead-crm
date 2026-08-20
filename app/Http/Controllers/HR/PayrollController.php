<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeTax;
use App\Models\Payroll;
use App\Models\TaxSlab;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = (int) $request->get('month', Carbon::now()->month);
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        $query = Payroll::with(['employee.department', 'employee.designation'])
            ->where('month', $selectedMonth)
            ->where('year', $selectedYear);

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('employee', function ($q) use ($s) {
                $q->where('first_name', 'LIKE', "%{$s}%")
                  ->orWhere('last_name', 'LIKE', "%{$s}%")
                  ->orWhere('employee_code', 'LIKE', "%{$s}%");
            });
        }

        // Summary totals for the filtered batch
        $summaryGross = (clone $query)->sum('gross_salary');
        $summaryTax = (clone $query)->sum('tax');
        $summaryPf = (clone $query)->sum('pf_deduction');
        $summaryNet = (clone $query)->sum('net_salary');

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $payrolls = $query->latest('generated_at')->paginate($perPage)->withQueryString();

        $departments = Department::where('status', 1)->get();
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $years = range(Carbon::now()->year - 2, Carbon::now()->year + 1);

        return view('hr.payrolls.index', compact(
            'payrolls',
            'departments',
            'selectedMonth',
            'selectedYear',
            'months',
            'years',
            'summaryGross',
            'summaryTax',
            'summaryPf',
            'summaryNet',
            'perPage'
        ));
    }

    public function create()
    {
        $employees = Employee::where('status', 'Active')
            ->whereHas('currentSalary')
            ->orderBy('first_name')
            ->get();

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $years = range(Carbon::now()->year - 1, Carbon::now()->year + 1);
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        return view('hr.payrolls.generate', compact('employees', 'months', 'years', 'currentMonth', 'currentYear'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required', // 'all' or specific employee_id
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2050',
            'include_tax' => 'nullable|boolean',
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];
        $includeTax = $request->boolean('include_tax', true);

        // Determine target employees
        if ($validated['employee_id'] === 'all') {
            $employees = Employee::where('status', 'Active')->with('currentSalary')->get();
        } else {
            $employees = Employee::where('id', $validated['employee_id'])->with('currentSalary')->get();
        }

        if ($employees->isEmpty()) {
            return back()->with('error', 'No active employees found to generate payroll.');
        }

        $generatedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $emp) {
                $salary = $emp->currentSalary;
                if (!$salary) {
                    $skippedCount++;
                    continue;
                }

                // 1. Calculate Monthly TDS Tax from active Tax Slabs
                $monthlyTax = 0.00;
                if ($includeTax && $salary->annual_salary > 0) {
                    $standardDeduction = 75000; // Standard FY deduction
                    $taxableIncome = max(0, $salary->annual_salary - $standardDeduction);
                    $annualTax = TaxSlab::calculateAnnualTax($taxableIncome);
                    $monthlyTax = round($annualTax / 12, 2);
                }

                $gross = (float) $salary->gross_salary;
                $pf = (float) $salary->pf_deduction;
                $otherDeduction = (float) $salary->other_deduction;
                $netSalary = max(0, $gross - $monthlyTax - $pf - $otherDeduction);

                // Create or overwrite payroll record with permanent snapshot
                $payroll = Payroll::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'basic_salary' => $salary->basic_salary,
                        'hra' => $salary->hra,
                        'allowances' => $salary->allowances,
                        'other_earnings' => $salary->other_earnings,
                        'gross_salary' => $gross,
                        'tax' => $monthlyTax,
                        'pf_deduction' => $pf,
                        'other_deduction' => $otherDeduction,
                        'net_salary' => $netSalary,
                        'status' => 'Generated',
                        'generated_at' => now(),
                    ]
                );

                // Update EmployeeTax record for the Financial Year
                $fyStartYear = ($month >= 4) ? $year : ($year - 1);
                $financialYear = $fyStartYear . '-' . ($fyStartYear + 1);

                $taxRecord = EmployeeTax::firstOrNew([
                    'employee_id' => $emp->id,
                    'financial_year' => $financialYear,
                ]);

                $taxRecord->annual_income = $salary->annual_salary;
                $taxRecord->taxable_income = max(0, $salary->annual_salary - 75000);
                $taxRecord->calculated_tax = TaxSlab::calculateAnnualTax($taxRecord->taxable_income);
                $taxRecord->paid_tax = ($taxRecord->paid_tax ?? 0) + $monthlyTax;
                $taxRecord->remaining_tax = max(0, $taxRecord->calculated_tax - $taxRecord->paid_tax);
                $taxRecord->save();

                $generatedCount++;
            }

            DB::commit();

            $msg = "Generated payroll for {$generatedCount} employees.";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} employees skipped due to missing salary structure).";
            }

            return redirect()->route('hr.payrolls.index', ['month' => $month, 'year' => $year])
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payroll generation failed: ' . $e->getMessage());
        }
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.designation', 'employee.manager']);
        return view('hr.payrolls.show', compact('payroll'));
    }

    public function payslip(Payroll $payroll)
    {
        $payroll->load(['employee.department', 'employee.designation', 'employee.manager']);
        return view('hr.payrolls.payslip', compact('payroll'));
    }

    public function updateStatus(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'status' => 'required|in:Draft,Generated,Paid,Cancelled',
        ]);

        $payroll->update($validated);

        return back()->with('success', "Payroll status updated to {$validated['status']}.");
    }
}
