@extends('layouts.app')

@section('title', 'Generate Monthly Payroll')
@section('page_title', 'Monthly Payroll Generator')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-wallet2 text-primary me-2"></i> Payroll Generation Wizard</h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info py-2.5 px-3 text-xs mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Automated Snapshot:</strong> This process creates an immutable salary, TDS tax, and deduction snapshot based on the current active salary structure and progressive tax slabs. Future salary modifications will not change past payrolls.
                </div>

                <form action="{{ route('hr.payrolls.generate') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Target Scope <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="all" selected>&starf; All Active Employees with Configured Salary ({{ count($employees) }} Staff)</option>
                            <option disabled>────────── Single Employee Run ──────────</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->full_name }} ({{ $emp->employee_code }} - {{ $emp->department->name ?? 'Staff' }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Payroll Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}" {{ $currentMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Payroll Year <span class="text-danger">*</span></label>
                            <select name="year" class="form-select @error('year') is-invalid @enderror" required>
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}" {{ $currentYear == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                                @endforeach
                            </select>
                            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4 p-3 bg-light rounded-3 ms-0">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="include_tax" value="1" id="includeTax" checked>
                        <label class="form-check-label fw-semibold text-sm" for="includeTax">
                            Compute & Withhold Income Tax (TDS) based on active Tax Slabs
                        </label>
                        <div class="text-xs text-muted ms-4">Automatically calculates annual taxable liability and withholds 1/12th as monthly TDS.</div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.payrolls.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" onclick="return confirm('Generate monthly payroll for the selected employees?');">
                            <i class="bi bi-gear-wide-connected me-1"></i> Run Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
