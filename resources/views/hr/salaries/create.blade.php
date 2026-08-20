@extends('layouts.app')

@section('title', 'Set Employee Salary')
@section('page_title', 'Structure Employee Salary')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-wallet2 text-primary me-2"></i> Employee Compensation Structuring</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.salaries.store') }}" method="POST" id="salaryForm">
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Select Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">Choose Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id', $selectedEmployeeId) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }} ({{ $emp->employee_code }} - {{ $emp->department->name ?? 'Staff' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Effective From Date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" class="form-control @error('effective_from') is-invalid @enderror" value="{{ old('effective_from', date('Y-m-01')) }}" required>
                            <div class="form-text text-xs">Previous active salary will be archived automatically up to day before this date.</div>
                            @error('effective_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Earnings Components -->
                    <h6 class="text-uppercase text-xs fw-bold text-success tracking-wider mb-3 pt-3 border-top">
                        <i class="bi bi-plus-circle me-1"></i> 1. Monthly Earnings Components
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Basic Salary (₹ / month) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control salary-input @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', $selectedEmployee?->currentSalary?->basic_salary ?? '25000') }}" required>
                            @error('basic_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">House Rent Allowance - HRA (₹ / month) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="hra" id="hra" class="form-control salary-input @error('hra') is-invalid @enderror" value="{{ old('hra', $selectedEmployee?->currentSalary?->hra ?? '10000') }}" required>
                            @error('hra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Special / Other Allowances (₹ / month)</label>
                            <input type="number" step="0.01" name="allowances" id="allowances" class="form-control salary-input @error('allowances') is-invalid @enderror" value="{{ old('allowances', $selectedEmployee?->currentSalary?->allowances ?? '5000') }}">
                            @error('allowances') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Other Variable / Performance Earnings (₹ / month)</label>
                            <input type="number" step="0.01" name="other_earnings" id="other_earnings" class="form-control salary-input @error('other_earnings') is-invalid @enderror" value="{{ old('other_earnings', $selectedEmployee?->currentSalary?->other_earnings ?? '0') }}">
                            @error('other_earnings') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Deductions Components -->
                    <h6 class="text-uppercase text-xs fw-bold text-danger tracking-wider mb-3 pt-3 border-top">
                        <i class="bi bi-dash-circle me-1"></i> 2. Monthly Deductions
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Provident Fund - PF / EPF (₹ / month)</label>
                            <input type="number" step="0.01" name="pf_deduction" id="pf_deduction" class="form-control salary-input @error('pf_deduction') is-invalid @enderror" value="{{ old('pf_deduction', $selectedEmployee?->currentSalary?->pf_deduction ?? '1800') }}">
                            @error('pf_deduction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Other Deductions / Professional Tax (₹ / month)</label>
                            <input type="number" step="0.01" name="other_deduction" id="other_deduction" class="form-control salary-input @error('other_deduction') is-invalid @enderror" value="{{ old('other_deduction', $selectedEmployee?->currentSalary?->other_deduction ?? '200') }}">
                            @error('other_deduction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Live Calculated Summary Preview -->
                    <div class="p-3.5 bg-light rounded-3 mb-4 border">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="text-xs text-muted text-uppercase fw-semibold">Monthly Gross Salary</div>
                                <div class="fs-4 fw-bold text-primary" id="previewGross">₹0.00</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-xs text-muted text-uppercase fw-semibold">Monthly Deductions</div>
                                <div class="fs-4 fw-bold text-danger" id="previewDeductions">₹0.00</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-xs text-muted text-uppercase fw-semibold">Annual CTC / Gross</div>
                                <div class="fs-4 fw-bold text-dark" id="previewAnnual">₹0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.salaries.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Salary Structure</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateCalculations() {
        const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
        const hra = parseFloat(document.getElementById('hra').value) || 0;
        const allowances = parseFloat(document.getElementById('allowances').value) || 0;
        const otherEarnings = parseFloat(document.getElementById('other_earnings').value) || 0;

        const pf = parseFloat(document.getElementById('pf_deduction').value) || 0;
        const otherDeduction = parseFloat(document.getElementById('other_deduction').value) || 0;

        const gross = basic + hra + allowances + otherEarnings;
        const deductions = pf + otherDeduction;
        const annual = gross * 12;

        document.getElementById('previewGross').innerText = '₹' + gross.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('previewDeductions').innerText = '₹' + deductions.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('previewAnnual').innerText = '₹' + annual.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.querySelectorAll('.salary-input').forEach(input => {
        input.addEventListener('input', updateCalculations);
    });

    updateCalculations();
</script>
@endpush
