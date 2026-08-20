<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeTax;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\TaxSlab;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Departments
        $deptExec = Department::firstOrCreate(['name' => 'Executive & Management'], [
            'description' => 'Executive leadership, corporate strategy, and operations.',
            'status' => 1,
        ]);
        $deptSales = Department::firstOrCreate(['name' => 'Sales & Business Development'], [
            'description' => 'Inbound/outbound sales, deal conversion, and client relationships.',
            'status' => 1,
        ]);
        $deptTech = Department::firstOrCreate(['name' => 'Information Technology'], [
            'description' => 'Software engineering, CRM infrastructure, and DevOps.',
            'status' => 1,
        ]);
        $deptHR = Department::firstOrCreate(['name' => 'Human Resources & People'], [
            'description' => 'Recruitment, employee engagement, compensation, and payroll.',
            'status' => 1,
        ]);
        $deptFinance = Department::firstOrCreate(['name' => 'Finance & Accounts'], [
            'description' => 'Invoicing, compliance, tax filings, and budgeting.',
            'status' => 1,
        ]);

        // 2. Seed Designations
        $desigMD = Designation::firstOrCreate(['name' => 'Managing Director / CEO'], [
            'description' => 'Overall corporate strategy and leadership.',
            'status' => 1,
        ]);
        $desigSalesMgr = Designation::firstOrCreate(['name' => 'Sales & Operations Manager'], [
            'description' => 'Manages sales team, quotas, and pipeline velocity.',
            'status' => 1,
        ]);
        $desigSalesExec = Designation::firstOrCreate(['name' => 'Senior Sales Executive'], [
            'description' => 'Lead qualification, pitching, client demos, and closures.',
            'status' => 1,
        ]);
        $desigTelecaller = Designation::firstOrCreate(['name' => 'Inside Sales / Telecaller'], [
            'description' => 'Lead qualification, follow-ups, and inbound response.',
            'status' => 1,
        ]);
        $desigHRExec = Designation::firstOrCreate(['name' => 'HR Operations Specialist'], [
            'description' => 'Employee lifecycle, onboarding, and payroll processing.',
            'status' => 1,
        ]);

        // 3. Seed Indian Tax Regime Slabs
        TaxSlab::truncate();
        $slabs = [
            ['name' => 'Up to ₹3,00,000 (Tax Free)', 'min_income' => 0, 'max_income' => 300000, 'tax_rate' => 0, 'fixed_tax' => 0, 'status' => 1],
            ['name' => '₹3,00,001 to ₹6,00,000 (5%)', 'min_income' => 300000, 'max_income' => 600000, 'tax_rate' => 5, 'fixed_tax' => 0, 'status' => 1],
            ['name' => '₹6,00,001 to ₹9,00,000 (10%)', 'min_income' => 600000, 'max_income' => 900000, 'tax_rate' => 10, 'fixed_tax' => 0, 'status' => 1],
            ['name' => '₹9,00,001 to ₹12,00,000 (15%)', 'min_income' => 900000, 'max_income' => 1200000, 'tax_rate' => 15, 'fixed_tax' => 0, 'status' => 1],
            ['name' => '₹12,00,001 to ₹15,00,000 (20%)', 'min_income' => 1200000, 'max_income' => 1500000, 'tax_rate' => 20, 'fixed_tax' => 0, 'status' => 1],
            ['name' => 'Above ₹15,00,000 (30%)', 'min_income' => 1500000, 'max_income' => null, 'tax_rate' => 30, 'fixed_tax' => 0, 'status' => 1],
        ];
        foreach ($slabs as $s) {
            TaxSlab::create($s);
        }

        // 4. Seed Leave Types
        $cl = LeaveType::firstOrCreate(['name' => 'Casual Leave (CL)'], [
            'total_days' => 12,
            'is_paid' => true,
            'description' => 'Unforeseen personal events or short personal off.',
            'status' => 1,
        ]);
        $sl = LeaveType::firstOrCreate(['name' => 'Sick / Medical Leave (SL)'], [
            'total_days' => 10,
            'is_paid' => true,
            'description' => 'Absence due to illness or medical requirements.',
            'status' => 1,
        ]);
        $pl = LeaveType::firstOrCreate(['name' => 'Earned / Privilege Leave (PL)'], [
            'total_days' => 15,
            'is_paid' => true,
            'description' => 'Planned vacation or annual leave.',
            'status' => 1,
        ]);
        $unpaid = LeaveType::firstOrCreate(['name' => 'Leave Without Pay (LWP)'], [
            'total_days' => 30,
            'is_paid' => false,
            'description' => 'Unpaid extended absence.',
            'status' => 1,
        ]);

        // 5. Seed Employees linked to existing CRM users
        $adminUser = User::where('email', 'admin@crm.com')->first();
        $hrUser = User::where('email', 'hr@crm.com')->first();
        $managerUser = User::where('email', 'manager@crm.com')->first();
        $salesUser = User::where('email', 'sales@crm.com')->first();
        $telecallerUser = User::where('email', 'telecaller@crm.com')->first();

        // 5.1 Admin Employee (Top Level)
        $adminEmp = Employee::firstOrCreate(['email' => 'admin@crm.com'], [
            'employee_code' => 'EMP-0001',
            'user_id' => $adminUser?->id,
            'department_id' => $deptExec->id,
            'designation_id' => $desigMD->id,
            'manager_id' => null,
            'first_name' => 'Vikram',
            'last_name' => 'Aditya',
            'phone' => '+91 98765 00001',
            'date_of_birth' => '1985-05-15',
            'gender' => 'Male',
            'joining_date' => '2023-01-01',
            'address' => 'Plot 42, Cyber City, Gurugram, Haryana',
            'status' => 'Active',
        ]);

        // 5.2 HR Employee
        $hrEmp = Employee::firstOrCreate(['email' => 'hr@crm.com'], [
            'employee_code' => 'EMP-0005',
            'user_id' => $hrUser?->id,
            'department_id' => $deptHR->id,
            'designation_id' => $desigHRExec->id,
            'manager_id' => $adminEmp->id,
            'first_name' => 'Ananya',
            'last_name' => 'Iyer',
            'phone' => '+91 98250 66666',
            'date_of_birth' => '1992-07-12',
            'gender' => 'Female',
            'joining_date' => '2023-02-01',
            'address' => '402, Prestige Tower, Indiranagar, Bengaluru, Karnataka',
            'status' => 'Active',
        ]);

        // 5.3 Manager Employee
        $mgrEmp = Employee::firstOrCreate(['email' => 'manager@crm.com'], [
            'employee_code' => 'EMP-0002',
            'user_id' => $managerUser?->id,
            'department_id' => $deptSales->id,
            'designation_id' => $desigSalesMgr->id,
            'manager_id' => $adminEmp->id,
            'first_name' => 'Pooja',
            'last_name' => 'Sharma',
            'phone' => '+91 98765 00002',
            'date_of_birth' => '1990-08-20',
            'gender' => 'Female',
            'joining_date' => '2023-03-15',
            'address' => 'A-102, Bandra Kurla Complex, Mumbai, Maharashtra',
            'status' => 'Active',
        ]);

        // 5.4 Salesperson Employee
        $salesEmp = Employee::firstOrCreate(['email' => 'sales@crm.com'], [
            'employee_code' => 'EMP-0003',
            'user_id' => $salesUser?->id,
            'department_id' => $deptSales->id,
            'designation_id' => $desigSalesExec->id,
            'manager_id' => $mgrEmp->id,
            'first_name' => 'David',
            'last_name' => 'Miller',
            'phone' => '+91 98765 00003',
            'date_of_birth' => '1995-11-10',
            'gender' => 'Male',
            'joining_date' => '2023-06-01',
            'address' => '12/4, Koramangala 5th Block, Bengaluru, Karnataka',
            'status' => 'Active',
        ]);

        // 5.5 Telecaller Employee
        $telecallerEmp = Employee::firstOrCreate(['email' => 'telecaller@crm.com'], [
            'employee_code' => 'EMP-0004',
            'user_id' => $telecallerUser?->id,
            'department_id' => $deptSales->id,
            'designation_id' => $desigTelecaller->id,
            'manager_id' => $mgrEmp->id,
            'first_name' => 'Sarah',
            'last_name' => 'Jenkins',
            'phone' => '+91 98765 00004',
            'date_of_birth' => '1998-04-25',
            'gender' => 'Female',
            'joining_date' => '2023-09-01',
            'address' => 'Flat 304, Hinjewadi Phase 1, Pune, Maharashtra',
            'status' => 'Active',
        ]);

        // 6. Seed Salary Structures for Employees
        $salaryProfiles = [
            $adminEmp->id => ['basic' => 90000, 'hra' => 36000, 'allowances' => 24000, 'other' => 0, 'pf' => 1800, 'other_ded' => 200],
            $hrEmp->id => ['basic' => 45000, 'hra' => 18000, 'allowances' => 12000, 'other' => 3000, 'pf' => 1800, 'other_ded' => 200],
            $mgrEmp->id => ['basic' => 50000, 'hra' => 20000, 'allowances' => 15000, 'other' => 5000, 'pf' => 1800, 'other_ded' => 200],
            $salesEmp->id => ['basic' => 35000, 'hra' => 14000, 'allowances' => 10000, 'other' => 3000, 'pf' => 1800, 'other_ded' => 200],
            $telecallerEmp->id => ['basic' => 20000, 'hra' => 8000, 'allowances' => 5000, 'other' => 2000, 'pf' => 1800, 'other_ded' => 200],
        ];

        foreach ($salaryProfiles as $empId => $data) {
            $gross = $data['basic'] + $data['hra'] + $data['allowances'] + $data['other'];
            EmployeeSalary::firstOrCreate(['employee_id' => $empId, 'status' => 1], [
                'basic_salary' => $data['basic'],
                'hra' => $data['hra'],
                'allowances' => $data['allowances'],
                'other_earnings' => $data['other'],
                'gross_salary' => $gross,
                'pf_deduction' => $data['pf'],
                'other_deduction' => $data['other_ded'],
                'annual_salary' => $gross * 12,
                'effective_from' => '2024-04-01',
                'status' => 1,
            ]);
        }

        // 7. Seed Leave Allocations (Year 2026)
        $currentYear = Carbon::now()->year;
        $allEmployees = [$adminEmp, $mgrEmp, $salesEmp, $telecallerEmp];
        $allLeaveTypes = [$cl, $sl, $pl];

        foreach ($allEmployees as $emp) {
            foreach ($allLeaveTypes as $lt) {
                LeaveAllocation::firstOrCreate([
                    'employee_id' => $emp->id,
                    'leave_type_id' => $lt->id,
                    'year' => $currentYear,
                ], [
                    'allocated_days' => $lt->total_days,
                    'used_days' => 0,
                    'remaining_days' => $lt->total_days,
                ]);
            }
        }

        // 8. Seed a sample Approved Leave Request for Salesperson
        $salesAlloc = LeaveAllocation::where('employee_id', $salesEmp->id)->where('leave_type_id', $cl->id)->where('year', $currentYear)->first();
        if ($salesAlloc && $salesAlloc->used_days == 0) {
            LeaveRequest::create([
                'employee_id' => $salesEmp->id,
                'leave_type_id' => $cl->id,
                'from_date' => Carbon::now()->addDays(5)->toDateString(),
                'to_date' => Carbon::now()->addDays(6)->toDateString(),
                'total_days' => 2,
                'reason' => 'Family wedding function in hometown.',
                'status' => 'Approved',
                'approved_by' => $adminUser?->id,
                'approved_at' => now(),
            ]);

            $salesAlloc->update([
                'used_days' => 2,
                'remaining_days' => max(0, $salesAlloc->allocated_days - 2),
            ]);
        }

        // 9. Seed a sample Payroll record for current month
        $curMonth = Carbon::now()->month;
        foreach ($allEmployees as $emp) {
            $sal = $emp->currentSalary;
            if ($sal) {
                $standardDeduction = 75000;
                $taxableIncome = max(0, $sal->annual_salary - $standardDeduction);
                $annualTax = TaxSlab::calculateAnnualTax($taxableIncome);
                $monthlyTax = round($annualTax / 12, 2);
                $net = max(0, $sal->gross_salary - $monthlyTax - $sal->pf_deduction - $sal->other_deduction);

                Payroll::firstOrCreate([
                    'employee_id' => $emp->id,
                    'month' => $curMonth,
                    'year' => $currentYear,
                ], [
                    'basic_salary' => $sal->basic_salary,
                    'hra' => $sal->hra,
                    'allowances' => $sal->allowances,
                    'other_earnings' => $sal->other_earnings,
                    'gross_salary' => $sal->gross_salary,
                    'tax' => $monthlyTax,
                    'pf_deduction' => $sal->pf_deduction,
                    'other_deduction' => $sal->other_deduction,
                    'net_salary' => $net,
                    'status' => 'Generated',
                    'generated_at' => now(),
                ]);
            }
        }
    }
}
