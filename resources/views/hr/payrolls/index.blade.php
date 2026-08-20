@extends('layouts.app')

@section('title', 'Payroll Management')
@section('page_title', 'Monthly Payroll & Salary Disbursement')

@section('content')
<!-- Month & Year Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.payrolls.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="month" class="form-select">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-select">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Generated" {{ request('status') === 'Generated' ? 'selected' : '' }}>Generated</option>
                    <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Load Batch</button>
                @if(request()->hasAny(['department_id', 'status', 'search']))
                    <a href="{{ route('hr.payrolls.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Payroll Batch Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Total Gross Payout</span>
            <div class="fs-4 fw-bold text-dark mb-0">@inr($summaryGross)</div>
            <div class="text-xs text-muted">{{ $payrolls->total() }} Employees in batch</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Total TDS Tax Withheld</span>
            <div class="fs-4 fw-bold text-danger mb-0">@inr($summaryTax)</div>
            <div class="text-xs text-muted">To be remitted to ITD</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Total PF Deductions</span>
            <div class="fs-4 fw-bold text-danger mb-0">@inr($summaryPf)</div>
            <div class="text-xs text-muted">EPFO Contribution pool</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-success-subtle border-success">
            <span class="text-xs text-success-emphasis text-uppercase fw-semibold">Net Disbursement</span>
            <div class="fs-4 fw-bold text-success mb-0">@inr($summaryNet)</div>
            <div class="text-xs text-muted">Total take-home payable</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-wallet2 text-primary me-1"></i> Payroll Batch: {{ $months[$selectedMonth] }} {{ $selectedYear }}</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $payrolls->total() }} Employees</span>
        </div>
        <a href="{{ route('hr.payrolls.generate.form') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Run / Generate Payroll
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department</th>
                        <th>Basic & HRA</th>
                        <th>Allowances</th>
                        <th>Gross Pay</th>
                        <th>TDS Tax</th>
                        <th>PF / Deductions</th>
                        <th>Net Take-Home</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Payslip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $pay)
                        @php
                            $statusBadge = match($pay->status) {
                                'Paid' => 'bg-success-subtle text-success',
                                'Generated' => 'bg-primary-subtle text-primary',
                                'Draft' => 'bg-warning-subtle text-warning',
                                'Cancelled' => 'bg-danger-subtle text-danger',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('hr.employees.show', $pay->employee_id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $pay->employee->full_name }}
                                </a>
                                <div class="text-xs text-muted">{{ $pay->employee->employee_code }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $pay->employee->department->name ?? 'Staff' }}</span>
                            </td>
                            <td class="text-xs">
                                <div>Basic: @inr($pay->basic_salary)</div>
                                <div class="text-muted">HRA: @inr($pay->hra)</div>
                            </td>
                            <td class="text-xs">
                                @inr($pay->allowances + $pay->other_earnings)
                            </td>
                            <td class="fw-bold text-dark">@inr($pay->gross_salary)</td>
                            <td class="text-danger fw-semibold">@inr($pay->tax)</td>
                            <td class="text-danger">@inr($pay->pf_deduction + $pay->other_deduction)</td>
                            <td class="fw-bold text-success fs-6">@inr($pay->net_salary)</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm badge {{ $statusBadge }} dropdown-toggle border-0" data-bs-toggle="dropdown">
                                        {{ $pay->status }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        @foreach(['Draft', 'Generated', 'Paid', 'Cancelled'] as $st)
                                            <li>
                                                <form action="{{ route('hr.payrolls.status', $pay->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $st }}">
                                                    <button type="submit" class="dropdown-item py-1.5 text-xs {{ $pay->status === $st ? 'active fw-bold' : '' }}">
                                                        Mark as {{ $st }}
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('hr.payrolls.show', $pay->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="View Summary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('hr.payrolls.payslip', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" title="Print Payslip">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-muted"></i>
                                No payroll generated for {{ $months[$selectedMonth] }} {{ $selectedYear }}.
                                <div class="mt-2">
                                    <a href="{{ route('hr.payrolls.generate.form') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                        Generate Payroll for this month
                                    </a>
                                </div>
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
                    @if($payrolls->total() > 0)
                        Showing <strong>{{ $payrolls->firstItem() }}</strong> to <strong>{{ $payrolls->lastItem() }}</strong> of <strong>{{ $payrolls->total() }}</strong> records
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

            @if($payrolls->hasPages())
                <div>
                    {{ $payrolls->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
