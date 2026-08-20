# Add HR Management Module to Existing Lead CRM

I already have an existing **Lead CRM project developed in Laravel**.

I now want to add a complete but simple **HR Management Module** inside the existing Lead CRM.

## Important Existing Project Rules

You MUST first understand and follow the existing project's:

* Folder structure
* Routes structure
* Controller structure
* Model structure
* Migration naming
* Blade view structure
* Authentication system
* Middleware
* Role/permission system
* Validation style
* Pagination style
* Flash message style
* Form design
* Table/list design
* Button/action design
* Naming conventions

### VERY IMPORTANT

Do NOT redesign or restructure my existing project.

Do NOT introduce a new architecture.

Do NOT create Service classes.

Do NOT create Repository classes.

Do NOT create unnecessary interfaces or abstract classes.

Keep the implementation simple and beginner/fresher friendly.

Use the same coding style already used in my Lead CRM.

Business logic should remain mainly inside:

* Controllers
* Models
* Form Requests, only if the existing project already uses them
* Blade views

Follow the existing project pattern wherever possible.

---

# HR Module Requirements

## 1. Employee Management

HR/Admin should be able to:

* Add employee
* Edit employee
* View employee
* Delete/deactivate employee
* Search employee
* Filter employee
* View employee details
* Assign department
* Assign designation
* Assign reporting manager
* Set joining date
* Set employee status

Employee fields should include:

* Employee Code
* User
* First Name
* Last Name
* Email
* Phone
* Date of Birth
* Gender
* Joining Date
* Department
* Designation
* Reporting Manager
* Address
* Status

Employee status:

* Active
* Inactive
* Resigned
* Terminated

---

# 2. Department Management

Create department management.

HR/Admin can:

* Add department
* Edit department
* View department
* Delete/deactivate department
* Search department

Fields:

* ID
* Name
* Description
* Status

---

# 3. Designation Management

Create designation management.

HR/Admin can:

* Add designation
* Edit designation
* View designation
* Delete/deactivate designation

Fields:

* ID
* Name
* Description
* Status

---

# 4. Reporting Manager

Each employee can have another employee as their reporting manager.

Use:

```text
employees.manager_id
```

The `manager_id` should reference:

```text
employees.id
```

Do NOT create a separate managers table.

An employee can have:

* One reporting manager
* Zero manager for top-level employees

Prevent an employee from selecting themselves as their own manager.

---

# 5. Employee Salary Management

HR/Admin should be able to manage employee salary.

Create salary history instead of storing salary directly inside the employee table.

Salary fields:

* Employee
* Basic Salary
* HRA
* Allowances
* Other Earnings
* Gross Salary
* PF/Other Deductions
* Other Deduction
* Annual Salary
* Effective From
* Effective To
* Status

Recommended calculation:

```text
Gross Salary =
Basic Salary
+ HRA
+ Allowances
+ Other Earnings
```

Do not delete old salary records when salary changes.

Create a new salary record with a new effective date so salary history is maintained.

---

# 6. Leave Type Management

HR/Admin can create leave types.

Examples:

* Casual Leave
* Sick Leave
* Paid Leave
* Unpaid Leave
* Other Leave

Fields:

* Name
* Total Days
* Paid/Unpaid
* Description
* Status

---

# 7. Leave Allocation

HR should allocate leaves to employees for each year.

Example:

```text
Employee: John
Year: 2026

Casual Leave:
Allocated: 12
Used: 4
Remaining: 8

Sick Leave:
Allocated: 10
Used: 2
Remaining: 8
```

Fields:

* Employee
* Leave Type
* Year
* Allocated Days
* Used Days
* Remaining Days

Remaining days should be calculated as:

```text
remaining_days = allocated_days - used_days
```

Do not allow used days to become greater than allocated days for paid/limited leave unless the existing business rules explicitly allow it.

---

# 8. Leave Request

Employees should be able to submit leave requests.

Fields:

* Employee
* Leave Type
* From Date
* To Date
* Total Days
* Reason
* Status
* Approved By
* Approved At
* Rejected Reason

Statuses:

* Pending
* Approved
* Rejected
* Cancelled

When an approved leave is created:

```text
leave_allocations.used_days
```

should be updated accordingly.

When an approved leave is cancelled or reverted, the used leave should be adjusted correctly.

Prevent overlapping approved leave requests for the same employee.

---

# 9. Leave Approval

Manager or HR/Admin should be able to:

* View pending leave requests
* View employee leave details
* Approve leave
* Reject leave
* Enter rejection reason

Only authorized users should be able to approve/reject leave according to the existing CRM role/permission system.

Do NOT create a new role system if the Lead CRM already has roles and permissions.

Reuse the existing authorization system.

---

# 10. Leave Report

Create an HR leave report.

Filters:

* Employee
* Department
* Manager
* Leave Type
* Status
* From Date
* To Date
* Year

Report should show:

* Employee
* Department
* Manager
* Leave Type
* From Date
* To Date
* Total Days
* Status
* Approved By

Also provide employee-wise summary:

```text
Employee
Total Allocated
Total Used
Total Remaining
```

---

# 11. Payroll

Create a simple monthly payroll module.

HR should be able to generate salary for an employee for a selected:

* Month
* Year

Payroll should calculate:

```text
Basic Salary
+ HRA
+ Allowances
+ Other Earnings
= Gross Salary

Gross Salary
- Tax
- PF/Other Deductions
- Other Deductions
= Net Salary
```

Payroll fields:

* Employee
* Month
* Year
* Basic Salary
* HRA
* Allowances
* Other Earnings
* Gross Salary
* Tax
* PF/Other Deductions
* Other Deductions
* Net Salary
* Status
* Generated At

Payroll statuses:

* Draft
* Generated
* Paid
* Cancelled

Once payroll is generated, keep a snapshot of salary values in the payroll table so future salary changes do not change old payroll records.

---

# 12. Tax Calculation

Add a tax calculation module.

Do NOT hard-code tax slab values inside the controller.

Create a `tax_slabs` table.

Fields:

* Name
* Minimum Income
* Maximum Income
* Tax Rate
* Fixed Tax
* Status

Tax calculation should follow this structure:

```text
Annual Salary
        ↓
Gross Annual Income
        ↓
Allowed Deductions
        ↓
Taxable Income
        ↓
Applicable Tax Slabs
        ↓
Annual Tax
        ↓
Monthly Tax
```

Create an `employee_taxes` table to maintain yearly employee tax information.

Fields:

* Employee
* Financial Year
* Annual Income
* Taxable Income
* Calculated Tax
* Paid Tax
* Remaining Tax

Important:

Tax rules can change by financial year.

Therefore, tax calculation must be designed so that tax slabs can be maintained separately.

Do not assume that today's Indian tax slabs will remain unchanged.

The admin/HR should be able to update tax slabs.

---

# 13. HR Dashboard

Create a simple HR dashboard using the existing dashboard design.

Display:

* Total Employees
* Active Employees
* Inactive Employees
* Employees on Leave Today
* Pending Leave Requests
* Current Month Payroll
* Total Monthly Salary
* Total Tax Deduction
* Department-wise Employee Count

Use the existing CRM UI components and dashboard style.

Do not introduce a new frontend framework.

---

# Database Tables

Create these tables:

## departments

```text
id
name
description
status
created_at
updated_at
```

## designations

```text
id
name
description
status
created_at
updated_at
```

## employees

```text
id
employee_code
user_id
department_id
designation_id
manager_id
first_name
last_name
email
phone
date_of_birth
gender
joining_date
address
status
created_at
updated_at
```

Relationships:

```text
user_id -> users.id
department_id -> departments.id
designation_id -> designations.id
manager_id -> employees.id
```

## employee_salaries

```text
id
employee_id
basic_salary
hra
allowances
other_earnings
gross_salary
pf_deduction
other_deduction
annual_salary
effective_from
effective_to
status
created_at
updated_at
```

## leave_types

```text
id
name
total_days
is_paid
description
status
created_at
updated_at
```

## leave_allocations

```text
id
employee_id
leave_type_id
year
allocated_days
used_days
remaining_days
created_at
updated_at
```

## leave_requests

```text
id
employee_id
leave_type_id
from_date
to_date
total_days
reason
status
approved_by
approved_at
rejected_reason
created_at
updated_at
```

## payrolls

```text
id
employee_id
month
year
basic_salary
hra
allowances
other_earnings
gross_salary
tax
pf_deduction
other_deduction
net_salary
status
generated_at
created_at
updated_at
```

## tax_slabs

```text
id
name
min_income
max_income
tax_rate
fixed_tax
status
created_at
updated_at
```

## employee_taxes

```text
id
employee_id
financial_year
annual_income
taxable_income
calculated_tax
paid_tax
remaining_tax
created_at
updated_at
```

---

# Laravel Structure

Follow my EXISTING Lead CRM folder structure.

If my existing project has:

```text
app/Models
app/Http/Controllers
resources/views
routes/web.php
database/migrations
```

then add the HR module using the same structure.

Example only:

```text
app/
├── Http/
│   └── Controllers/
│       └── HR/
│           ├── EmployeeController.php
│           ├── DepartmentController.php
│           ├── DesignationController.php
│           ├── LeaveTypeController.php
│           ├── LeaveAllocationController.php
│           ├── LeaveRequestController.php
│           ├── PayrollController.php
│           └── TaxController.php
│
├── Models/
│   ├── Employee.php
│   ├── Department.php
│   ├── Designation.php
│   ├── EmployeeSalary.php
│   ├── LeaveType.php
│   ├── LeaveAllocation.php
│   ├── LeaveRequest.php
│   ├── Payroll.php
│   ├── TaxSlab.php
│   └── EmployeeTax.php
│
resources/
└── views/
    └── hr/
        ├── dashboard.blade.php
        ├── employees/
        ├── departments/
        ├── designations/
        ├── salaries/
        ├── leave-types/
        ├── leave-allocations/
        ├── leave-requests/
        ├── payroll/
        └── taxes/
```

This is only a suggested structure. If my current project uses a different structure, FOLLOW MY CURRENT STRUCTURE instead.

---

# Coding Rules

Use simple Laravel code.

Prefer:

```php
public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
    ]);

    Department::create([
        'name' => $request->name,
    ]);

    return redirect()
        ->route('departments.index')
        ->with('success', 'Department created successfully.');
}
```

Do not create:

```text
DepartmentService.php
EmployeeService.php
PayrollService.php
Repository/
Contracts/
Interfaces/
DTO/
Actions/
```

unless the existing project already uses these patterns.

Keep controller logic straightforward and readable.

---

# Routes

Follow the existing Lead CRM route structure.

Use resource routes where appropriate:

```php
Route::resource('employees', EmployeeController::class);
Route::resource('departments', DepartmentController::class);
Route::resource('designations', DesignationController::class);
Route::resource('leave-types', LeaveTypeController::class);
Route::resource('leave-allocations', LeaveAllocationController::class);
Route::resource('leave-requests', LeaveRequestController::class);
```

Add custom routes only where necessary, such as:

```text
leave-requests/{leaveRequest}/approve
leave-requests/{leaveRequest}/reject
payroll/generate
payroll/report
tax-calculation
```

Use the existing project's route naming conventions.

---

# Validation

Validate:

* Required employee fields
* Valid email
* Unique employee code
* Valid department
* Valid designation
* Valid manager
* Salary must be numeric
* Leave dates must be valid
* To date must not be before from date
* Leave allocation must be valid
* Payroll month/year must be valid

Use the existing validation approach.

---

# Security

Follow the existing authentication and authorization system.

HR functionality must not automatically become accessible to every CRM user.

Use the existing:

* Middleware
* Roles
* Permissions
* Authentication

Do not create another authentication system.

Salary, payroll and tax information should only be accessible to authorized users.

---

# UI Requirements

Use the existing Lead CRM UI.

Do NOT redesign the application.

Follow the existing:

* Header
* Sidebar
* Breadcrumb
* Cards
* Buttons
* Forms
* Tables
* Pagination
* Modal
* Alerts
* Validation messages

The UI must be responsive.

Use the existing CSS/framework already installed in the project.

Do not add another CSS framework.

---

# Development Order

Implement the HR module in this order:

1. Departments
2. Designations
3. Employees
4. Employee/Manager relationship
5. Employee Salary
6. Leave Types
7. Leave Allocation
8. Leave Requests
9. Leave Approval
10. Leave Reports
11. Tax Slabs
12. Employee Tax
13. Payroll
14. HR Dashboard
15. Permissions
16. Final testing

---

# Important Testing

Test these cases:

### Employee

* Create employee
* Edit employee
* Assign manager
* Prevent self-manager
* Deactivate employee

### Salary

* Create salary
* Update salary
* Maintain salary history
* Verify gross salary
* Verify annual salary

### Leave

* Allocate leave
* Apply leave
* Approve leave
* Reject leave
* Prevent overlapping leave
* Verify used leave
* Verify remaining leave
* Cancel approved leave

### Payroll

* Generate monthly payroll
* Verify gross salary
* Verify tax
* Verify deductions
* Verify net salary
* Verify old payroll remains unchanged after salary update

### Tax

* Create tax slabs
* Calculate taxable income
* Calculate annual tax
* Calculate monthly tax
* Verify financial year
* Maintain employee tax history

---

# Final Requirement

Before writing code:

1. Inspect my existing Lead CRM structure.
2. Understand how the current Lead module is implemented.
3. Follow the same folder structure.
4. Follow the same controller style.
5. Follow the same model style.
6. Follow the same Blade structure.
7. Follow the same route structure.
8. Follow the same validation style.
9. Follow the same authorization system.
10. Do not introduce Service classes.
11. Do not introduce Repository classes.
12. Do not unnecessarily refactor existing code.
13. Keep the code simple enough for a fresher Laravel developer to understand.
14. Reuse existing components wherever possible.
15. Only add new files required for the HR module.

The HR module should feel like a natural extension of the existing Lead CRM, not a separate application.
