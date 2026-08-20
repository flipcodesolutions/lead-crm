@extends('layouts.app')

@section('title', 'Apply for Leave')
@section('page_title', 'Submit Leave Application')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Leave Balance Quotas Summary (If employee is selected or current user is employee) -->
        @if(count($allocations) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 px-4">
                    <h6 class="fw-bold m-0"><i class="bi bi-pie-chart text-primary me-2"></i> Current Leave Balances ({{ $currentYear }})</h6>
                </div>
                <div class="card-body p-3.5">
                    <div class="row g-3">
                        @foreach($allocations as $alloc)
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="text-xs text-muted fw-semibold">{{ $alloc->leaveType->name }}</div>
                                    <div class="fs-4 fw-bold text-success">{{ $alloc->remaining_days }}</div>
                                    <div class="text-xs text-muted">Remaining of {{ $alloc->allocated_days }} days</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-calendar2-plus text-primary me-2"></i> Leave Request Form</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.leave-requests.store') }}" method="POST" id="leaveForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Employee Name <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            @if($currentEmployee)
                                <option value="{{ $currentEmployee->id }}" selected>{{ $currentEmployee->full_name }} ({{ $currentEmployee->employee_code }})</option>
                            @endif
                            @if(auth()->user()->canManageHR())
                                @foreach($employees as $emp)
                                    @if(!$currentEmployee || $emp->id !== $currentEmployee->id)
                                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Leave Category <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                    {{ $lt->name }} ({{ $lt->is_paid ? 'Paid' : 'Unpaid' }})
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="from_date" id="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date', date('Y-m-d')) }}" required>
                            @error('from_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="to_date" id="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date', date('Y-m-d')) }}" required>
                            @error('to_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 text-xs mb-3" id="durationAlert">
                        <i class="bi bi-info-circle me-1"></i> Requested duration: <strong id="calculatedDays">1</strong> day(s)
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-sm">Reason for Absence <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" placeholder="Provide reason for leave (medical, family event, personal travel)..." required>{{ old('reason') }}</textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.leave-requests.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function calculateDuration() {
        const fromVal = document.getElementById('from_date').value;
        const toVal = document.getElementById('to_date').value;
        if (fromVal && toVal) {
            const from = new Date(fromVal);
            const to = new Date(toVal);
            if (to >= from) {
                const diffTime = Math.abs(to - from);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('calculatedDays').innerText = diffDays;
                document.getElementById('durationAlert').className = 'alert alert-info py-2 px-3 text-xs mb-3';
            } else {
                document.getElementById('calculatedDays').innerText = '0 (Invalid date range)';
                document.getElementById('durationAlert').className = 'alert alert-danger py-2 px-3 text-xs mb-3';
            }
        }
    }

    document.getElementById('from_date').addEventListener('change', calculateDuration);
    document.getElementById('to_date').addEventListener('change', calculateDuration);
    calculateDuration();
</script>
@endpush
