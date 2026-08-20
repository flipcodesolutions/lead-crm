@extends('layouts.app')

@section('title', 'Payroll Summary: ' . $payroll->employee->full_name)
@section('page_title', 'Payroll Details: ' . $payroll->employee->full_name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold m-0"><i class="bi bi-receipt-cutoff text-primary me-2"></i> Payroll Record: {{ $payroll->month_name }} {{ $payroll->year }}</h5>
                    <span class="text-xs text-muted">Generated on {{ $payroll->generated_at?->format('d M Y, h:i A') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('hr.payrolls.payslip', $payroll->id) }}" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                        <i class="bi bi-printer me-1"></i> Print Payslip
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Employee Summary Header -->
                <div class="p-3 bg-light rounded-3 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-xs text-muted text-uppercase d-block">Employee</span>
                            <div class="fw-bold text-dark fs-5">{{ $payroll->employee->full_name }}</div>
                            <span class="text-xs text-muted">{{ $payroll->employee->employee_code }} &bull; {{ $payroll->employee->department->name ?? 'Staff' }} ({{ $payroll->employee->designation->name ?? '' }})</span>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="text-xs text-muted text-uppercase d-block">Payment Status</span>
                            <span class="badge {{ $payroll->status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} fs-6">
                                {{ $payroll->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Earnings vs Deductions Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border h-100 shadow-none">
                            <div class="card-header bg-light py-2.5 px-3">
                                <h6 class="fw-bold text-success m-0"><i class="bi bi-plus-circle me-1"></i> Gross Earnings</h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0 text-sm">
                                    <tr>
                                        <td class="text-muted">Basic Salary:</td>
                                        <td class="text-end fw-semibold">@inr($payroll->basic_salary)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">House Rent Allowance (HRA):</td>
                                        <td class="text-end fw-semibold">@inr($payroll->hra)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Allowances:</td>
                                        <td class="text-end fw-semibold">@inr($payroll->allowances)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Other Earnings:</td>
                                        <td class="text-end fw-semibold">@inr($payroll->other_earnings)</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold text-dark pt-2">Total Gross Earnings:</td>
                                        <td class="text-end fw-bold text-primary pt-2 fs-6">@inr($payroll->gross_salary)</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border h-100 shadow-none">
                            <div class="card-header bg-light py-2.5 px-3">
                                <h6 class="fw-bold text-danger m-0"><i class="bi bi-dash-circle me-1"></i> Total Deductions</h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0 text-sm">
                                    <tr>
                                        <td class="text-muted">Income Tax (TDS):</td>
                                        <td class="text-end fw-semibold text-danger">@inr($payroll->tax)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Provident Fund (PF):</td>
                                        <td class="text-end fw-semibold text-danger">@inr($payroll->pf_deduction)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Other Deductions / PT:</td>
                                        <td class="text-end fw-semibold text-danger">@inr($payroll->other_deduction)</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold text-dark pt-2">Total Deductions:</td>
                                        <td class="text-end fw-bold text-danger pt-2 fs-6">@inr($payroll->tax + $payroll->pf_deduction + $payroll->other_deduction)</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Pay Banner -->
                <div class="p-3.5 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-4 text-center">
                    <span class="text-xs text-uppercase fw-bold text-success-emphasis d-block mb-1">Net Take-Home Salary Payable</span>
                    <div class="fs-2 fw-bold text-success mb-0">@inr($payroll->net_salary)</div>
                </div>

                <!-- Status Modification & Navigation -->
                <div class="d-flex justify-content-between pt-3 border-top">
                    <a href="{{ route('hr.payrolls.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}" class="btn btn-light rounded-pill px-4">
                        &larr; Back to Payrolls
                    </a>

                    <form action="{{ route('hr.payrolls.status', $payroll->id) }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            @foreach(['Draft', 'Generated', 'Paid', 'Cancelled'] as $st)
                                <option value="{{ $st }}" {{ $payroll->status === $st ? 'selected' : '' }}>Mark as {{ $st }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
