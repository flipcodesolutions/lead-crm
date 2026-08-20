@extends('layouts.app')

@section('title', 'Employees')
@section('page_title', 'Employee Directory')

@section('content')
<!-- Filter & Search Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.employees.index') }}" method="GET" class="row g-2">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, code, email, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="designation_id" class="form-select">
                    <option value="">All Designations</option>
                    @foreach($designations as $desig)
                        <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="Resigned" {{ request('status') === 'Resigned' ? 'selected' : '' }}>Resigned</option>
                    <option value="Terminated" {{ request('status') === 'Terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="manager_id" class="form-select">
                    <option value="">All Managers</option>
                    @foreach($managers as $mgr)
                        <option value="{{ $mgr->id }}" {{ request('manager_id') == $mgr->id ? 'selected' : '' }}>{{ $mgr->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 px-2" title="Filter"><i class="bi bi-funnel"></i></button>
                @if(request()->hasAny(['search', 'department_id', 'designation_id', 'status', 'manager_id']))
                    <a href="{{ route('hr.employees.index') }}" class="btn btn-light border px-2" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Employees Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-person-lines-fill text-primary me-1"></i> All Employees</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $employees->total() }} Total</span>
        </div>
        <a href="{{ route('hr.employees.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Employee
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department & Role</th>
                        <th>Contact</th>
                        <th>Reporting Manager</th>
                        <th>Monthly Gross</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="avatar-circle bg-primary text-white" style="width: 36px; height: 36px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('hr.employees.show', $emp->id) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $emp->full_name }}
                                        </a>
                                        <div class="text-xs text-muted">
                                            <span class="badge bg-light text-dark border">{{ $emp->employee_code }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-sm text-dark">{{ $emp->designation->name ?? 'General Staff' }}</div>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $emp->department->name ?? 'Unassigned' }}</span>
                            </td>
                            <td>
                                <div class="text-xs text-dark">{{ $emp->email }}</div>
                                <div class="text-xs text-muted">@phone($emp->phone)</div>
                            </td>
                            <td>
                                @if($emp->manager)
                                    <div class="text-xs fw-semibold text-dark">{{ $emp->manager->full_name }}</div>
                                    <span class="text-xs text-muted">{{ $emp->manager->designation->name ?? '' }}</span>
                                @else
                                    <span class="text-xs text-muted">Direct / Executive</span>
                                @endif
                            </td>
                            <td>
                                @if($emp->currentSalary)
                                    <div class="fw-bold text-dark">@inr($emp->currentSalary->gross_salary)</div>
                                    <span class="text-xs text-muted">@inr($emp->currentSalary->annual_salary)/yr</span>
                                @else
                                    <a href="{{ route('hr.salaries.create', ['employee_id' => $emp->id]) }}" class="badge bg-warning-subtle text-warning text-decoration-none">
                                        + Set Salary
                                    </a>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($emp->status) {
                                        'Active' => 'bg-success-subtle text-success',
                                        'Inactive' => 'bg-secondary-subtle text-secondary',
                                        'Resigned' => 'bg-warning-subtle text-warning',
                                        'Terminated' => 'bg-danger-subtle text-danger',
                                        default => 'bg-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} badge-status">{{ $emp->status }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-pill px-2" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('hr.employees.show', $emp->id) }}">
                                                <i class="bi bi-person-bounding-box me-2 text-primary"></i> View Full Profile
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('hr.employees.edit', $emp->id) }}">
                                                <i class="bi bi-pencil me-2 text-secondary"></i> Edit Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('hr.salaries.create', ['employee_id' => $emp->id]) }}">
                                                <i class="bi bi-cash-coin me-2 text-success"></i> Adjust Salary
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('hr.salaries.history', $emp->id) }}">
                                                <i class="bi bi-clock-history me-2 text-info"></i> Salary History
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('hr.employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Mark this employee as Inactive?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="bi bi-person-x me-2"></i> Deactivate Employee
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                                No employees found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($employees->total() > 0)
                        Showing <strong>{{ $employees->firstItem() }}</strong> to <strong>{{ $employees->lastItem() }}</strong> of <strong>{{ $employees->total() }}</strong> employees
                    @else
                        Showing 0 employees
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 ms-2">
                    <span class="text-muted text-xs">Show:</span>
                    <select class="form-select form-select-sm" style="width: auto; font-size: 0.8rem; padding: 0.25rem 0.6rem; border-radius: 0.375rem;" onchange="window.location.href = this.value">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                {{ $size }} per page
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($employees->hasPages())
                <div>
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
