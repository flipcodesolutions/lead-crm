@extends('layouts.app')

@section('title', 'Leave Audit Reports')
@section('page_title', 'Leave Utilization & Audit Reports')

@section('content')
<!-- Report Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.leave-reports.index') }}" method="GET" class="row g-2">
            <div class="col-md-2">
                <select name="year" class="form-select">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                    @endforeach
                </select>
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
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="leave_type_id" class="form-select">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                @if(request()->hasAny(['department_id', 'employee_id', 'leave_type_id', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('hr.leave-reports.index', ['year' => $selectedYear]) }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Employee-Wise Leave Balance Summary Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0"><i class="bi bi-people-fill text-primary me-2"></i> Employee-Wise Quota & Balance Summary ({{ $selectedYear }})</h6>
        <span class="badge bg-secondary-subtle text-secondary">{{ count($employeeSummaries) }} Active Staff</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Total Allocated</th>
                        <th>Total Used</th>
                        <th>Total Remaining</th>
                        <th>Overall Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeeSummaries as $item)
                        @php
                            $pct = $item->total_allocated > 0 ? min(100, round(($item->total_used / $item->total_allocated) * 100)) : 0;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('hr.employees.show', $item->employee->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $item->employee->full_name }}
                                </a>
                                <div class="text-xs text-muted">{{ $item->employee->employee_code }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $item->employee->department->name ?? 'Unassigned' }}</span>
                            </td>
                            <td class="text-xs">{{ $item->employee->designation->name ?? 'Staff' }}</td>
                            <td class="fw-bold text-dark">{{ $item->total_allocated }} days</td>
                            <td class="fw-bold text-danger">{{ $item->total_used }} days</td>
                            <td class="fw-bold text-success">{{ $item->total_remaining }} days</td>
                            <td style="min-width: 130px;">
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
                            <td colspan="7" class="text-center py-4 text-muted">No employee summaries available for this year.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Leave Log Entries Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0"><i class="bi bi-journal-text text-primary me-2"></i> Detailed Leave Applications Log</h6>
        <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $leaveRecords->total() }} Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department</th>
                        <th>Reporting Manager</th>
                        <th>Leave Type</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRecords as $rec)
                        @php
                            $badgeClass = match($rec->status) {
                                'Approved' => 'bg-success-subtle text-success',
                                'Pending' => 'bg-warning-subtle text-warning',
                                'Rejected' => 'bg-danger-subtle text-danger',
                                'Cancelled' => 'bg-secondary-subtle text-secondary',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $rec->employee->full_name }}</div>
                                <span class="text-xs text-muted">{{ $rec->employee->employee_code }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $rec->employee->department->name ?? '—' }}</span>
                            </td>
                            <td class="text-xs">{{ $rec->employee->manager->full_name ?? 'Direct' }}</td>
                            <td><span class="badge bg-info-subtle text-info">{{ $rec->leaveType->name }}</span></td>
                            <td class="text-xs">{{ $rec->from_date->format('d M Y') }}</td>
                            <td class="text-xs">{{ $rec->to_date->format('d M Y') }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $rec->total_days }} day(s)</span></td>
                            <td><span class="badge {{ $badgeClass }} badge-status">{{ $rec->status }}</span></td>
                            <td class="text-xs text-muted">{{ $rec->approver->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No leave log entries match the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($leaveRecords->total() > 0)
                        Showing <strong>{{ $leaveRecords->firstItem() }}</strong> to <strong>{{ $leaveRecords->lastItem() }}</strong> of <strong>{{ $leaveRecords->total() }}</strong> records
                    @else
                        Showing 0 records
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

            @if($leaveRecords->hasPages())
                <div>
                    {{ $leaveRecords->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
