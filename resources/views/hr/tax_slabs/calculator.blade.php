@extends('layouts.app')

@section('title', 'Income Tax Calculator')
@section('page_title', 'Interactive Income Tax Simulator')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0"><i class="bi bi-calculator-fill text-primary me-2"></i> Annual Income Tax & Monthly TDS Calculator</h5>
                <a href="{{ route('hr.tax-slabs.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-sliders me-1"></i> Manage Slabs
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.tax-calculator') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-sm">Gross Annual Income (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">₹</span>
                            <input type="number" step="1000" name="annual_income" class="form-control" value="{{ $annualGross ?: '750000' }}" placeholder="e.g. 900000" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-sm">Allowed Deductions / Exemptions (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">₹</span>
                            <input type="number" step="1000" name="deductions" class="form-control" value="{{ $deductions ?: '75000' }}" placeholder="Standard deduction: ₹75,000">
                        </div>
                        <div class="form-text text-xs">Standard FY deduction: ₹75,000 / ₹50,000</div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-arrow-clockwise me-1"></i> Calculate
                        </button>
                    </div>
                </form>

                <!-- Calculation Flow Visualizer Cards -->
                <div class="p-4 bg-light rounded-3 border mb-4">
                    <div class="row text-center g-3">
                        <div class="col-md-3">
                            <div class="text-xs text-muted text-uppercase fw-semibold">Gross Annual Salary</div>
                            <div class="fs-4 fw-bold text-dark">@inr($annualGross)</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-xs text-muted text-uppercase fw-semibold">Allowed Deductions</div>
                            <div class="fs-4 fw-bold text-danger">&minus; @inr($deductions)</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-xs text-muted text-uppercase fw-semibold">Taxable Income</div>
                            <div class="fs-4 fw-bold text-primary">@inr($taxableIncome)</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-xs text-muted text-uppercase fw-semibold">Annual Tax Liability</div>
                            <div class="fs-4 fw-bold text-danger">@inr($annualTax)</div>
                            <div class="badge bg-warning-subtle text-warning">Monthly TDS: @inr($monthlyTax)</div>
                        </div>
                    </div>
                </div>

                <!-- Active Slabs Breakdown -->
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-layers text-primary me-2"></i> Slab-by-Slab Calculation Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Income Bracket</th>
                                <th>Tax Rate</th>
                                <th>Taxable Amount in Bracket</th>
                                <th class="text-end">Computed Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $runningTax = 0;
                            @endphp
                            @foreach($slabs as $slab)
                                @php
                                    $amountInSlab = 0;
                                    $taxInSlab = 0;
                                    if ($taxableIncome > $slab->min_income) {
                                        $upper = $slab->max_income !== null ? min($taxableIncome, $slab->max_income) : $taxableIncome;
                                        $amountInSlab = max(0, $upper - $slab->min_income);
                                        $taxInSlab = ($amountInSlab * ($slab->tax_rate / 100)) + ($amountInSlab > 0 ? $slab->fixed_tax : 0);
                                        $runningTax += $taxInSlab;
                                    }
                                @endphp
                                <tr class="{{ $amountInSlab > 0 ? 'table-primary bg-opacity-25' : '' }}">
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $slab->name }}</div>
                                        <span class="text-xs text-muted">@inr($slab->min_income) to {{ $slab->max_income ? '₹' . number_format($slab->max_income, 2) : 'Above' }}</span>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $slab->tax_rate }}%</span></td>
                                    <td class="fw-semibold">@inr($amountInSlab)</td>
                                    <td class="text-end fw-bold {{ $taxInSlab > 0 ? 'text-danger' : 'text-muted' }}">@inr($taxInSlab)</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end text-uppercase text-xs">Total Calculated Annual Income Tax:</th>
                                <th class="text-end text-danger fs-6 fw-bold">@inr($annualTax)</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
