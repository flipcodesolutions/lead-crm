<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'manager', 'currentSalary', 'user']);

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'LIKE', "%{$s}%")
                  ->orWhere('last_name', 'LIKE', "%{$s}%")
                  ->orWhere('employee_code', 'LIKE', "%{$s}%")
                  ->orWhere('email', 'LIKE', "%{$s}%")
                  ->orWhere('phone', 'LIKE', "%{$s}%");
            });
        }

        // Filters
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $employees = $query->latest()->paginate($perPage)->withQueryString();

        $departments = Department::where('status', 1)->get();
        $designations = Designation::where('status', 1)->get();
        $managers = Employee::where('status', 'Active')->get();

        return view('hr.employees.index', compact('employees', 'departments', 'designations', 'managers', 'perPage'));
    }

    public function create()
    {
        $departments = Department::where('status', 1)->get();
        $designations = Designation::where('status', 1)->get();
        $managers = Employee::where('status', 'Active')->get();
        $users = User::where('status', 1)->whereDoesntHave('employee')->get();

        // Auto-generate employee code
        $nextId = (Employee::max('id') ?? 0) + 1;
        $suggestedCode = 'EMP-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
        while (Employee::where('employee_code', $suggestedCode)->exists()) {
            $nextId++;
            $suggestedCode = 'EMP-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
        }

        return view('hr.employees.create', compact('departments', 'designations', 'managers', 'users', 'suggestedCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email',
            'phone' => 'nullable|string|max:30',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'manager_id' => 'nullable|exists:employees,id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'joining_date' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive,Resigned,Terminated',
        ]);

        Employee::create($validated);

        return redirect()->route('hr.employees.index')->with('success', 'Employee profile created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'department',
            'designation',
            'manager',
            'subordinates',
            'user',
            'salaries' => function ($q) {
                $q->latest('effective_from');
            },
            'currentSalary',
            'leaveAllocations.leaveType',
            'leaveRequests' => function ($q) {
                $q->with('leaveType')->latest();
            },
            'payrolls' => function ($q) {
                $q->latest('year')->latest('month');
            },
            'taxes' => function ($q) {
                $q->latest('financial_year');
            },
        ]);

        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('status', 1)->get();
        $designations = Designation::where('status', 1)->get();
        // Prevent selecting self as reporting manager
        $managers = Employee::where('id', '!=', $employee->id)->where('status', 'Active')->get();
        $users = User::where('status', 1)
            ->where(function ($q) use ($employee) {
                $q->whereDoesntHave('employee')
                  ->orWhere('id', $employee->user_id);
            })->get();

        return view('hr.employees.edit', compact('employee', 'departments', 'designations', 'managers', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:30',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'manager_id' => 'nullable|exists:employees,id|different:id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'joining_date' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive,Resigned,Terminated',
        ]);

        // Explicitly prevent self-manager assignment
        if (isset($validated['manager_id']) && (int)$validated['manager_id'] === (int)$employee->id) {
            return back()->withInput()->with('error', 'An employee cannot be their own reporting manager.');
        }

        $employee->update($validated);

        return redirect()->route('hr.employees.show', $employee->id)->with('success', 'Employee profile updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        // Deactivate employee
        $employee->update(['status' => 'Inactive']);

        return redirect()->route('hr.employees.index')->with('success', 'Employee status marked as Inactive.');
    }
}
