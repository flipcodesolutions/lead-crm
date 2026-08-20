@extends('layouts.app')

@section('title', 'HR Dashboard')
@section('page_title', 'HR & Payroll Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <!-- Total Employees Metric -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-uppercase fw-bold text-muted tracking-wider">Total Headcount</span>
                    <div class="avatar-circle bg-primary-subtle text-primary" style="width: 40px; height: 40px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $totalEmployees }}</h3>
                <div class="d-flex align-items-center gap-2 text-xs">
                    <span class="badge bg-success-subtle text-success">{{ $activeEmployees }} Active</span>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $inactiveEmployees }} Inactive</span>
                </div>
            </div>
        </div>
    </div>

    <!-- On Leave Today -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-uppercase fw-bold text-muted tracking-wider">On Leave Today</span>
                    <div class="avatar-circle bg-warning-subtle text-warning" style="width: 40px; height: 40px;">
                        <i class="bi bi-calendar2-x-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $employeesOnLeaveToday->count() }}</h3>
                <div class="text-xs text-muted">
                    {{ $employeesOnLeaveToday->count() > 0 ? 'Employees currently on approved absence' : 'Full attendance today' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Leave Requests -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-uppercase fw-bold text-muted tracking-wider">Pending Leave Approvals</span>
                    <div class="avatar-circle bg-info-subtle text-info" style="width: 40px; height: 40px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $pendingLeaveRequests->count() }}</h3>
                <div class="text-xs">
                    <a href="{{ route('hr.leave-requests.index', ['status' => 'Pending']) }}" class="text-primary fw-semibold text-decoration-none">
                        Review requests &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Payroll Cost -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-uppercase fw-bold text-muted tracking-wider">Monthly Payroll Liability</span>
                    <div class="avatar-circle bg-success-subtle text-success" style="width: 40px; height: 40px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">@inr($totalMonthlyPayroll)</h3>
                <div class="text-xs text-muted">
                    Annual: <strong>@inr($totalAnnualPayroll)</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Employees on Leave Today & Pending Approvals -->
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-calendar2-check text-primary me-2"></i> Employees on Leave Today
                </h6>
                <span class="badge bg-light text-muted border">{{ date('d M Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Department</th>
                                <th>Leave Dates</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeesOnLeaveToday as $leave)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $leave->employee->full_name }}</div>
                                        <span class="text-xs text-muted">{{ $leave->employee->employee_code }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $leave->employee->department->name ?? 'General' }}</span>
                                    </td>
                                    <td class="text-xs">
                                        {{ $leave->from_date->format('d M') }} &mdash; {{ $leave->to_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $leave->total_days }} day(s)</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted text-sm">
                                        <i class="bi bi-check2-circle text-success fs-4 d-block mb-1"></i>
                                        All employees are present today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Leave Requests Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-hourglass-split text-warning me-2"></i> Pending Leave Requests
                </h6>
                <a href="{{ route('hr.leave-requests.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none">View All &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Reason</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingLeaveRequests as $req)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $req->employee->full_name }}</div>
                                        <span class="text-xs text-muted">{{ $req->employee->department->name ?? '' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">{{ $req->leaveType->name }}</span>
                                    </td>
                                    <td class="text-xs">
                                        {{ $req->from_date->format('d M') }} to {{ $req->to_date->format('d M') }} ({{ $req->total_days }}d)
                                    </td>
                                    <td class="text-xs text-muted text-truncate" style="max-width: 160px;" title="{{ $req->reason }}">
                                        {{ $req->reason }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form action="{{ route('hr.leave-requests.approve', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('hr.leave-requests.show', $req->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Review">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted text-sm">
                                        No pending leave approval requests.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Headcount & Payroll Quick Launch -->
    <div class="col-xl-5">
        <!-- Department Distribution -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-building text-primary me-2"></i> Department Breakdown
                </h6>
                <a href="{{ route('hr.departments.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none">Manage &rarr;</a>
            </div>
            <div class="card-body p-4">
                @forelse($departments as $dept)
                    @php
                        $pct = $totalEmployees > 0 ? round(($dept->employees_count / $totalEmployees) * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-xs fw-semibold mb-1">
                            <span>{{ $dept->name }}</span>
                            <span>{{ $dept->employees_count }} staff ({{ $pct }}%)</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-sm text-center py-3">No departments created yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Payroll Quick Action Card -->
        <div class="card border-0 shadow-sm bg-primary text-white" style="background: var(--primary-gradient) !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-circle bg-white text-primary" style="width: 46px; height: 46px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-white mb-0">Monthly Payroll Run</h5>
                        <p class="text-xs text-white-50 mb-0">Generate, review, and print payslips</p>
                    </div>
                </div>
                <div class="p-3 bg-white bg-opacity-10 rounded-3 mb-3 text-xs">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Current Month Processed:</span>
                        <strong>{{ $currentMonthPayrollCount }} Employees</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Net Payout:</span>
                        <strong>@inr($currentMonthPaidPayrollSum)</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>TDS Tax Withheld:</span>
                        <strong>@inr($currentMonthTaxDeduction)</strong>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('hr.payrolls.generate.form') }}" class="btn btn-light rounded-pill w-100 fw-semibold text-primary">
                        <i class="bi bi-plus-circle me-1"></i> Generate Payroll
                    </a>
                    <a href="{{ route('hr.payrolls.index') }}" class="btn btn-outline-light rounded-pill w-100">
                        View All
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
