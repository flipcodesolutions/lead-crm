@extends('layouts.app')

@section('title', 'Employee Salaries')
@section('page_title', 'Compensation & Salary Structures')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.salaries.index') }}" method="GET" class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search employee name or code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Department::where('status', 1)->get() as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['search', 'department_id']))
                    <a href="{{ route('hr.salaries.index') }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-cash-stack text-primary me-1"></i> Active Salary Structures</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $salaries->total() }} Records</span>
        </div>
        <a href="{{ route('hr.salaries.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Structure Salary
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department & Role</th>
                        <th>Basic Salary</th>
                        <th>HRA</th>
                        <th>Allowances</th>
                        <th>Monthly Gross</th>
                        <th>PF Deduction</th>
                        <th>Annual CTC</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $sal)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="{{ route('hr.employees.show', $sal->employee_id) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $sal->employee->full_name }}
                                        </a>
                                        <div class="text-xs text-muted">{{ $sal->employee->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs fw-semibold text-dark">{{ $sal->employee->designation->name ?? 'Staff' }}</div>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $sal->employee->department->name ?? 'Unassigned' }}</span>
                            </td>
                            <td>@inr($sal->basic_salary)</td>
                            <td>@inr($sal->hra)</td>
                            <td>@inr($sal->allowances + $sal->other_earnings)</td>
                            <td class="fw-bold text-primary">@inr($sal->gross_salary)</td>
                            <td class="text-danger">@inr($sal->pf_deduction)</td>
                            <td class="fw-bold text-dark">@inr($sal->annual_salary)</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('hr.salaries.create', ['employee_id' => $sal->employee_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" title="Revise Salary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('hr.salaries.history', $sal->employee_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="History">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No salary structures configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($salaries->total() > 0)
                        Showing <strong>{{ $salaries->firstItem() }}</strong> to <strong>{{ $salaries->lastItem() }}</strong> of <strong>{{ $salaries->total() }}</strong> salaries
                    @else
                        Showing 0 salaries
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

            @if($salaries->hasPages())
                <div>
                    {{ $salaries->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
