<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeTax;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\TaxSlab;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $salesperson;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic masters
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\HRSeeder::class);

        $this->admin = User::where('email', 'admin@crm.com')->firstOrFail();
        $this->manager = User::where('email', 'manager@crm.com')->firstOrFail();
        $this->salesperson = User::where('email', 'sales@crm.com')->firstOrFail();
    }

    /**
     * 1. Test Department CRUD
     */
    public function test_department_management(): void
    {
        $this->actingAs($this->admin)->get('/hr/departments')->assertStatus(200)->assertSee('Executive & Management');

        // Create
        $response = $this->actingAs($this->admin)->post('/hr/departments', [
            'name' => 'Quality Assurance',
            'description' => 'Software quality & testing unit',
            'status' => 1,
        ]);
        $response->assertRedirect('/hr/departments');
        $this->assertDatabaseHas('departments', ['name' => 'Quality Assurance']);

        // Update
        $dept = Department::where('name', 'Quality Assurance')->firstOrFail();
        $this->actingAs($this->admin)->put("/hr/departments/{$dept->id}", [
            'name' => 'Quality & Automation',
            'description' => 'Updated desc',
            'status' => 1,
        ])->assertRedirect('/hr/departments');
        $this->assertDatabaseHas('departments', ['name' => 'Quality & Automation']);

        // Delete
        $this->actingAs($this->admin)->delete("/hr/departments/{$dept->id}")->assertRedirect('/hr/departments');
        $this->assertDatabaseMissing('departments', ['name' => 'Quality & Automation']);
    }

    /**
     * 2. Test Designation CRUD
     */
    public function test_designation_management(): void
    {
        $this->actingAs($this->admin)->get('/hr/designations')->assertStatus(200);

        // Create
        $this->actingAs($this->admin)->post('/hr/designations', [
            'name' => 'Lead DevOps Engineer',
            'description' => 'CI/CD and Cloud maintenance',
            'status' => 1,
        ])->assertRedirect('/hr/designations');
        $this->assertDatabaseHas('designations', ['name' => 'Lead DevOps Engineer']);
    }

    /**
     * 3. Test Employee Management & Self-Manager Prevention
     */
    public function test_employee_creation_and_self_manager_prevention(): void
    {
        $dept = Department::first();
        $desig = Designation::first();

        // 3.1 Create new employee
        $response = $this->actingAs($this->admin)->post('/hr/employees', [
            'employee_code' => 'EMP-9999',
            'first_name' => 'Rohan',
            'last_name' => 'Verma',
            'email' => 'rohan.verma@crm.com',
            'phone' => '+91 98765 43210',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);
        $response->assertRedirect('/hr/employees');
        $this->assertDatabaseHas('employees', ['employee_code' => 'EMP-9999', 'first_name' => 'Rohan']);

        $rohan = Employee::where('employee_code', 'EMP-9999')->firstOrFail();

        // 3.2 Try to assign himself as his own manager (Must Fail / Prevent)
        $respSelf = $this->actingAs($this->admin)->put("/hr/employees/{$rohan->id}", [
            'employee_code' => 'EMP-9999',
            'first_name' => 'Rohan',
            'last_name' => 'Verma',
            'email' => 'rohan.verma@crm.com',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'manager_id' => $rohan->id, // Self manager!
            'status' => 'Active',
        ]);
        $respSelf->assertSessionHas('error');
        $this->assertNull($rohan->fresh()->manager_id);

        // 3.3 Deactivate Employee
        $this->actingAs($this->admin)->delete("/hr/employees/{$rohan->id}")->assertRedirect('/hr/employees');
        $this->assertEquals('Inactive', $rohan->fresh()->status);
    }

    /**
     * 4. Test Salary History, Gross Salary & Annual Calculations
     */
    public function test_salary_structuring_and_history_maintenance(): void
    {
        $employee = Employee::where('employee_code', 'EMP-0003')->firstOrFail(); // Salesperson

        // 4.1 Check initial salary
        $curSal = $employee->currentSalary;
        $this->assertNotNull($curSal);
        $this->assertEquals(1, $curSal->status);

        // 4.2 Revise Salary (Increment)
        $newEffective = Carbon::now()->addMonth()->startOfMonth()->toDateString();
        $this->actingAs($this->admin)->post('/hr/salaries', [
            'employee_id' => $employee->id,
            'basic_salary' => 50000,
            'hra' => 20000,
            'allowances' => 15000,
            'other_earnings' => 5000,
            'pf_deduction' => 1800,
            'other_deduction' => 200,
            'effective_from' => $newEffective,
        ])->assertRedirect();

        // Verify Gross & Annual calculations
        // Gross = 50000 + 20000 + 15000 + 5000 = 90,000
        // Annual = 90,000 * 12 = 1,080,000
        $revisedSal = $employee->fresh()->currentSalary;
        $this->assertEquals(90000.00, (float) $revisedSal->gross_salary);
        $this->assertEquals(1080000.00, (float) $revisedSal->annual_salary);
        $this->assertEquals(1, $revisedSal->status);

        // Verify Old Salary is archived with status=0 and effective_to set
        $oldSal = EmployeeSalary::where('employee_id', $employee->id)->where('status', 0)->firstOrFail();
        $this->assertEquals(0, $oldSal->status);
        $this->assertNotNull($oldSal->effective_to);
    }

    /**
     * 5. Test Leave Allocation, Overlap Validation, Approval & Cancellation
     */
    public function test_leave_workflow_and_quota_calculations(): void
    {
        $employee = Employee::where('employee_code', 'EMP-0004')->firstOrFail(); // Telecaller
        $cl = LeaveType::where('name', 'Casual Leave (CL)')->firstOrFail();
        $year = Carbon::now()->year;

        // 5.1 Check allocation
        $alloc = LeaveAllocation::where('employee_id', $employee->id)->where('leave_type_id', $cl->id)->where('year', $year)->firstOrFail();
        $this->assertEquals(12.0, (float) $alloc->allocated_days);
        $this->assertEquals(12.0, (float) $alloc->remaining_days);

        // 5.2 Submit 3-day leave request
        $fromDate = Carbon::now()->addDays(10)->toDateString();
        $toDate = Carbon::now()->addDays(12)->toDateString();

        $this->actingAs($this->telecallerUser ?? $this->admin)->post('/hr/leave-requests', [
            'employee_id' => $employee->id,
            'leave_type_id' => $cl->id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'reason' => 'Family vacation trip',
        ])->assertRedirect('/hr/leave-requests');

        $req = LeaveRequest::where('employee_id', $employee->id)->where('reason', 'Family vacation trip')->firstOrFail();
        $this->assertEquals('Pending', $req->status);
        $this->assertEquals(3.0, (float) $req->total_days);

        // 5.3 Approve leave
        $this->actingAs($this->admin)->post("/hr/leave-requests/{$req->id}/approve")->assertRedirect('/hr/leave-requests');
        $this->assertEquals('Approved', $req->fresh()->status);

        // Verify balance updated: used=3, remaining=9
        $allocFresh = $alloc->fresh();
        $this->assertEquals(3.0, (float) $allocFresh->used_days);
        $this->assertEquals(9.0, (float) $allocFresh->remaining_days);

        // 5.4 Test Overlapping Leave Prevention (Must Fail)
        $respOverlap = $this->actingAs($this->admin)->post('/hr/leave-requests', [
            'employee_id' => $employee->id,
            'leave_type_id' => $cl->id,
            'from_date' => Carbon::now()->addDays(11)->toDateString(), // Overlaps 10-12
            'to_date' => Carbon::now()->addDays(14)->toDateString(),
            'reason' => 'Another overlapping leave',
        ]);
        $respOverlap->assertSessionHas('error');

        // 5.5 Cancel approved leave and verify balance is refunded!
        $this->actingAs($this->admin)->post("/hr/leave-requests/{$req->id}/cancel")->assertRedirect('/hr/leave-requests');
        $this->assertEquals('Cancelled', $req->fresh()->status);
        $this->assertEquals(0.0, (float) $alloc->fresh()->used_days);
        $this->assertEquals(12.0, (float) $alloc->fresh()->remaining_days);
    }

    /**
     * 6. Test Progressive Tax Slab Calculations
     */
    public function test_progressive_tax_slab_calculations(): void
    {
        // Indian Tax Slabs (Standard Old/New progressive calculation test):
        // Up to 3L: 0%
        // 3L to 6L (3L @ 5%): ₹15,000
        // 6L to 9L (3L @ 10%): ₹30,000
        // 9L to 12L (3L @ 15%): ₹45,000
        // 12L to 15L (3L @ 20%): ₹60,000
        // Above 15L (remainder @ 30%)

        // Income: ₹6,00,000 -> Tax should be ₹15,000
        $tax6L = TaxSlab::calculateAnnualTax(600000);
        $this->assertEquals(15000.00, $tax6L);

        // Income: ₹9,00,000 -> Tax should be ₹15,000 + ₹30,000 = ₹45,000
        $tax9L = TaxSlab::calculateAnnualTax(900000);
        $this->assertEquals(45000.00, $tax9L);

        // Income: ₹15,00,000 -> Tax should be 15,000 + 30,000 + 45,000 + 60,000 = ₹1,50,000
        $tax15L = TaxSlab::calculateAnnualTax(1500000);
        $this->assertEquals(150000.00, $tax15L);
    }

    /**
     * 7. Test Monthly Payroll Generation & Immutable Snapshot
     */
    public function test_monthly_payroll_generation_and_snapshot_immutability(): void
    {
        $employee = Employee::where('employee_code', 'EMP-0002')->firstOrFail(); // Manager

        // 7.1 Generate payroll for next month
        $month = Carbon::now()->addMonth()->month;
        $year = Carbon::now()->addMonth()->year;

        $this->actingAs($this->admin)->post('/hr/payrolls/generate', [
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'include_tax' => 1,
        ])->assertRedirect();

        $payroll = Payroll::where('employee_id', $employee->id)->where('month', $month)->where('year', $year)->firstOrFail();
        $this->assertEquals('Generated', $payroll->status);
        $this->assertGreaterThan(0, (float) $payroll->gross_salary);
        $this->assertGreaterThan(0, (float) $payroll->net_salary);

        $originalGross = $payroll->gross_salary;
        $originalNet = $payroll->net_salary;

        // 7.2 Now modify employee's salary structure
        $this->actingAs($this->admin)->post('/hr/salaries', [
            'employee_id' => $employee->id,
            'basic_salary' => 120000,
            'hra' => 48000,
            'allowances' => 32000,
            'effective_from' => Carbon::now()->addMonths(2)->startOfMonth()->toDateString(),
        ]);

        // 7.3 Assert that previously generated payroll record snapshot remains completely unchanged!
        $this->assertEquals($originalGross, $payroll->fresh()->gross_salary);
        $this->assertEquals($originalNet, $payroll->fresh()->net_salary);
    }

    /**
     * 8. Test HR Dashboard Accessibility & Metrics
     */
    public function test_hr_dashboard_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get('/hr/dashboard');
        $response->assertStatus(200);
        $response->assertSee('HR &amp; Payroll Dashboard', false);
        $response->assertSee('Total Headcount');
        $response->assertSee('Monthly Payroll Liability');
    }

    /**
     * 9. Test Dedicated HR Role & User Access
     */
    public function test_hr_role_and_user_access(): void
    {
        $hrUser = User::where('email', 'hr@crm.com')->firstOrFail();
        $this->assertTrue($hrUser->isHR());
        $this->assertTrue($hrUser->canManageHR());

        // HR user accesses dashboard
        $this->actingAs($hrUser)->get('/hr/dashboard')->assertStatus(200);

        // HR user accesses employees
        $this->actingAs($hrUser)->get('/hr/employees')->assertStatus(200);

        // HR user accesses departments
        $this->actingAs($hrUser)->get('/hr/departments')->assertStatus(200);

        // HR user accesses designations
        $this->actingAs($hrUser)->get('/hr/designations')->assertStatus(200);

        // HR user accesses salaries
        $this->actingAs($hrUser)->get('/hr/salaries')->assertStatus(200);

        // HR user accesses leave allocations
        $this->actingAs($hrUser)->get('/hr/leave-allocations')->assertStatus(200);

        // HR user accesses leave requests
        $this->actingAs($hrUser)->get('/hr/leave-requests')->assertStatus(200);

        // HR user accesses leave reports
        $this->actingAs($hrUser)->get('/hr/leave-reports')->assertStatus(200);

        // HR user accesses payrolls
        $this->actingAs($hrUser)->get('/hr/payrolls')->assertStatus(200);

        // HR user accesses tax slabs
        $this->actingAs($hrUser)->get('/hr/tax-slabs')->assertStatus(200);
    }
}
