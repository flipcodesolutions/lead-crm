@extends('layouts.app')

@section('title', $employee->full_name . ' - Employee Profile')
@section('page_title', 'Employee Profile: ' . $employee->full_name)

@section('content')
<!-- Top Hero Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-circle bg-primary text-white" style="width: 64px; height: 64px; font-size: 1.5rem; font-weight: 700;">
                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name ?? '', 0, 1)) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold text-dark mb-0">{{ $employee->full_name }}</h4>
                        <span class="badge bg-light text-dark border">{{ $employee->employee_code }}</span>
                        @php
                            $statusClass = match($employee->status) {
                                'Active' => 'bg-success-subtle text-success',
                                'Inactive' => 'bg-secondary-subtle text-secondary',
                                'Resigned' => 'bg-warning-subtle text-warning',
                                'Terminated' => 'bg-danger-subtle text-danger',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }} badge-status">{{ $employee->status }}</span>
                    </div>
                    <p class="text-sm text-muted mb-0">
                        <i class="bi bi-person-badge text-primary me-1"></i> {{ $employee->designation->name ?? 'General Staff' }}
                        &bull;
                        <i class="bi bi-building text-secondary me-1"></i> {{ $employee->department->name ?? 'Unassigned' }}
                        @if($employee->joining_date)
                            &bull; <i class="bi bi-calendar3 text-muted me-1"></i> Joined {{ $employee->joining_date->format('d M Y') }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('hr.salaries.create', ['employee_id' => $employee->id]) }}" class="btn btn-outline-success rounded-pill btn-sm px-3">
                    <i class="bi bi-cash-coin me-1"></i> Update Salary
                </a>
                <a href="{{ route('hr.leave-requests.create', ['employee_id' => $employee->id]) }}" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                    <i class="bi bi-calendar-plus me-1"></i> Apply Leave
                </a>
                <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn btn-primary rounded-pill btn-sm px-3">
                    <i class="bi bi-pencil me-1"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Detail Panes -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-0 border-bottom">
        <ul class="nav nav-tabs card-header-tabs m-0 px-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active py-3 text-sm fw-semibold" data-bs-toggle="tab" href="#tab-overview">
                    <i class="bi bi-person me-1"></i> Profile & Hierarchy
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 text-sm fw-semibold" data-bs-toggle="tab" href="#tab-salary">
                    <i class="bi bi-cash-stack me-1"></i> Salary & Compensation History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 text-sm fw-semibold" data-bs-toggle="tab" href="#tab-leaves">
                    <i class="bi bi-calendar2-check me-1"></i> Leaves & Allocations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 text-sm fw-semibold" data-bs-toggle="tab" href="#tab-payrolls">
                    <i class="bi bi-receipt me-1"></i> Monthly Payslips
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 text-sm fw-semibold" data-bs-toggle="tab" href="#tab-tax">
                    <i class="bi bi-bank me-1"></i> Tax & TDS
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content">
            <!-- 1. Overview & Hierarchy Tab -->
            <div class="tab-pane fade show active" id="tab-overview">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Personal Details</h6>
                            <div class="row g-2 text-sm">
                                <div class="col-sm-5 text-muted">Full Name:</div>
                                <div class="col-sm-7 fw-semibold text-dark">{{ $employee->full_name }}</div>

                                <div class="col-sm-5 text-muted">Email Address:</div>
                                <div class="col-sm-7 fw-semibold text-dark">{{ $employee->email }}</div>

                                <div class="col-sm-5 text-muted">Phone Number:</div>
                                <div class="col-sm-7 fw-semibold text-dark">@phone($employee->phone)</div>

                                <div class="col-sm-5 text-muted">Date of Birth:</div>
                                <div class="col-sm-7 text-dark">{{ $employee->date_of_birth ? $employee->date_of_birth->format('d M Y') : '—' }}</div>

                                <div class="col-sm-5 text-muted">Gender:</div>
                                <div class="col-sm-7 text-dark">{{ $employee->gender ?: '—' }}</div>

                                <div class="col-sm-5 text-muted">Residential Address:</div>
                                <div class="col-sm-7 text-dark">{{ $employee->address ?: '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-3 text-primary me-2"></i> Organizational Hierarchy</h6>
                            <div class="row g-2 text-sm mb-3">
                                <div class="col-sm-5 text-muted">Department:</div>
                                <div class="col-sm-7 fw-semibold text-dark">{{ $employee->department->name ?? 'Unassigned' }}</div>

                                <div class="col-sm-5 text-muted">Designation:</div>
                                <div class="col-sm-7 fw-semibold text-dark">{{ $employee->designation->name ?? 'General Staff' }}</div>

                                <div class="col-sm-5 text-muted">Reporting Manager:</div>
                                <div class="col-sm-7 fw-semibold text-dark">
                                    @if($employee->manager)
                                        <a href="{{ route('hr.employees.show', $employee->manager->id) }}" class="text-primary text-decoration-none">
                                            {{ $employee->manager->full_name }}
                                        </a>
                                        <span class="text-xs text-muted">({{ $employee->manager->designation->name ?? 'Manager' }})</span>
                                    @else
                                        <span class="text-muted">Direct / Executive (No Manager)</span>
                                    @endif
                                </div>

                                <div class="col-sm-5 text-muted">CRM User Account:</div>
                                <div class="col-sm-7 text-dark">
                                    @if($employee->user)
                                        <span class="badge bg-success-subtle text-success">{{ $employee->user->email }} ({{ $employee->user->role->name ?? 'User' }})</span>
                                    @else
                                        <span class="text-muted">Not linked to a CRM login</span>
                                    @endif
                                </div>
                            </div>

                            @if($employee->subordinates->count() > 0)
                                <div class="pt-2 border-top">
                                    <span class="text-xs text-uppercase fw-bold text-muted d-block mb-1">Direct Reportees ({{ $employee->subordinates->count() }}):</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($employee->subordinates as $sub)
                                            <a href="{{ route('hr.employees.show', $sub->id) }}" class="badge bg-white border text-dark text-decoration-none py-1.5 px-2">
                                                {{ $sub->full_name }} <span class="text-muted">({{ $sub->designation->name ?? '' }})</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Salary & Compensation Tab -->
            <div class="tab-pane fade" id="tab-salary">
                @if($employee->currentSalary)
                    <div class="p-4 bg-light rounded-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark m-0">Current Compensation Structure</h6>
                            <span class="badge bg-success-subtle text-success">Effective from {{ $employee->currentSalary->effective_from->format('d M Y') }}</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-xs text-muted text-uppercase">Basic Salary</div>
                                <div class="fs-5 fw-bold text-dark">@inr($employee->currentSalary->basic_salary)</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-xs text-muted text-uppercase">HRA</div>
                                <div class="fs-5 fw-bold text-dark">@inr($employee->currentSalary->hra)</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-xs text-muted text-uppercase">Allowances & Other</div>
                                <div class="fs-5 fw-bold text-dark">@inr($employee->currentSalary->allowances + $employee->currentSalary->other_earnings)</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-xs text-muted text-uppercase">Monthly Gross</div>
                                <div class="fs-5 fw-bold text-primary">@inr($employee->currentSalary->gross_salary)</div>
                                <div class="text-xs text-muted">Annual: @inr($employee->currentSalary->annual_salary)</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> No active salary structure set for this employee yet.
                        </div>
                        <a href="{{ route('hr.salaries.create', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-warning">
                            Set Salary Structure
                        </a>
                    </div>
                @endif

                <!-- Salary Timeline History -->
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i> Historical Salary Revisions</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Effective Period</th>
                                <th>Basic</th>
                                <th>HRA</th>
                                <th>Allowances</th>
                                <th>Gross Salary</th>
                                <th>PF Deduction</th>
                                <th>Annual CTC</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->salaries as $sal)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $sal->effective_from->format('d M Y') }}</div>
                                        <span class="text-xs text-muted">{{ $sal->effective_to ? 'to ' . $sal->effective_to->format('d M Y') : 'Present' }}</span>
                                    </td>
                                    <td>@inr($sal->basic_salary)</td>
                                    <td>@inr($sal->hra)</td>
                                    <td>@inr($sal->allowances + $sal->other_earnings)</td>
                                    <td class="fw-bold text-primary">@inr($sal->gross_salary)</td>
                                    <td class="text-danger">@inr($sal->pf_deduction)</td>
                                    <td class="fw-bold text-dark">@inr($sal->annual_salary)</td>
                                    <td>
                                        <span class="badge {{ $sal->status == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $sal->status == 1 ? 'Active' : 'Archived' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">No past salary records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Leaves & Allocations Tab -->
            <div class="tab-pane fade" id="tab-leaves">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark m-0">Leave Quotas ({{ date('Y') }})</h6>
                    <a href="{{ route('hr.leave-allocations.create', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-plus-lg me-1"></i> Allocate Leaves
                    </a>
                </div>

                <div class="row g-3 mb-4">
                    @forelse($employee->leaveAllocations as $alloc)
                        <div class="col-md-4">
                            <div class="card border p-3 rounded-3 shadow-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">{{ $alloc->leaveType->name }}</span>
                                    <span class="badge bg-light text-muted">{{ $alloc->year }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                    <span>Allocated: <strong>{{ $alloc->allocated_days }}</strong></span>
                                    <span>Used: <strong>{{ $alloc->used_days }}</strong></span>
                                </div>
                                <div class="progress mb-2" style="height: 6px;">
                                    @php
                                        $pct = $alloc->allocated_days > 0 ? round(($alloc->used_days / $alloc->allocated_days) * 100) : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="text-xs fw-bold text-success text-end">
                                    {{ $alloc->remaining_days }} Days Remaining
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light text-muted border text-sm text-center py-3">
                                No leave quota allocated for this employee yet.
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Recent Leave Applications -->
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-primary me-2"></i> Leave History</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->leaveRequests as $req)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $req->leaveType->name }}</td>
                                    <td class="text-xs">{{ $req->from_date->format('d M') }} &mdash; {{ $req->to_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $req->total_days }} day(s)</span></td>
                                    <td class="text-xs text-muted text-truncate" style="max-width: 200px;">{{ $req->reason }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($req->status) {
                                                'Approved' => 'bg-success-subtle text-success',
                                                'Pending' => 'bg-warning-subtle text-warning',
                                                'Rejected' => 'bg-danger-subtle text-danger',
                                                'Cancelled' => 'bg-secondary-subtle text-secondary',
                                                default => 'bg-light text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} badge-status">{{ $req->status }}</span>
                                    </td>
                                    <td class="text-xs text-muted">
                                        {{ $req->approver->name ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No leave requests recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Monthly Payslips Tab -->
            <div class="tab-pane fade" id="tab-payrolls">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Period</th>
                                <th>Gross Salary</th>
                                <th>TDS Tax</th>
                                <th>PF Deduction</th>
                                <th>Other Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->payrolls as $pay)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        {{ $pay->month_name }} {{ $pay->year }}
                                    </td>
                                    <td>@inr($pay->gross_salary)</td>
                                    <td class="text-danger">@inr($pay->tax)</td>
                                    <td class="text-danger">@inr($pay->pf_deduction)</td>
                                    <td class="text-danger">@inr($pay->other_deduction)</td>
                                    <td class="fw-bold text-success fs-6">@inr($pay->net_salary)</td>
                                    <td>
                                        <span class="badge {{ $pay->status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                                            {{ $pay->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('hr.payrolls.payslip', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2.5">
                                            <i class="bi bi-printer me-1"></i> Payslip
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No payrolls generated yet for this employee.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. Tax & TDS Tab -->
            <div class="tab-pane fade" id="tab-tax">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Financial Year</th>
                                <th>Annual Gross</th>
                                <th>Taxable Income</th>
                                <th>Calculated Annual Tax</th>
                                <th>TDS Paid</th>
                                <th>Remaining Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->taxes as $tax)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $tax->financial_year }}</td>
                                    <td>@inr($tax->annual_income)</td>
                                    <td>@inr($tax->taxable_income)</td>
                                    <td class="fw-semibold text-danger">@inr($tax->calculated_tax)</td>
                                    <td class="fw-semibold text-success">@inr($tax->paid_tax)</td>
                                    <td class="fw-bold text-dark">@inr($tax->remaining_tax)</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No tax computation history available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
