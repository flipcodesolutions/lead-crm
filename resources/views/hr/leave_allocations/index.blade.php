@extends('layouts.app')

@section('title', 'Leave Allocations')
@section('page_title', 'Employee Leave Quota Allocations')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.leave-allocations.index') }}" method="GET" class="row g-2">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search employee name/code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-select">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="leave_type_id" class="form-select">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['search', 'leave_type_id', 'employee_id']))
                    <a href="{{ route('hr.leave-allocations.index', ['year' => $selectedYear]) }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-calendar2-range text-primary me-1"></i> Leave Allocations for Year {{ $selectedYear }}</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $allocations->total() }} Records</span>
        </div>
        <a href="{{ route('hr.leave-allocations.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Allocate Leaves
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Year</th>
                        <th>Allocated</th>
                        <th>Used</th>
                        <th>Remaining Balance</th>
                        <th>Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allocations as $alloc)
                        @php
                            $pct = $alloc->allocated_days > 0 ? min(100, round(($alloc->used_days / $alloc->allocated_days) * 100)) : 0;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('hr.employees.show', $alloc->employee_id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $alloc->employee->full_name }}
                                </a>
                                <div class="text-xs text-muted">{{ $alloc->employee->employee_code }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $alloc->employee->department->name ?? 'Unassigned' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info">{{ $alloc->leaveType->name }}</span>
                            </td>
                            <td>{{ $alloc->year }}</td>
                            <td class="fw-bold text-dark">{{ $alloc->allocated_days }} days</td>
                            <td class="text-danger fw-semibold">{{ $alloc->used_days }} days</td>
                            <td class="fw-bold text-success">{{ $alloc->remaining_days }} days</td>
                            <td style="min-width: 140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-muted">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No leave allocations found for year {{ $selectedYear }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($allocations->total() > 0)
                        Showing <strong>{{ $allocations->firstItem() }}</strong> to <strong>{{ $allocations->lastItem() }}</strong> of <strong>{{ $allocations->total() }}</strong> allocations
                    @else
                        Showing 0 allocations
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

            @if($allocations->hasPages())
                <div>
                    {{ $allocations->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
