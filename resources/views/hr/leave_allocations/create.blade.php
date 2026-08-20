@extends('layouts.app')

@section('title', 'Allocate Leaves')
@section('page_title', 'Allocate Employee Leave Quotas')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-calendar2-plus text-primary me-2"></i> Leave Allocation Form</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.leave-allocations.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Target Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="all" {{ old('employee_id') === 'all' ? 'selected' : '' }}>&starf; All Active Employees (Bulk Grant)</option>
                            <option disabled>────────── Specific Employee ──────────</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', $selectedEmployeeId) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->employee_code }} - {{ $emp->department->name ?? 'Staff' }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Leave Category <span class="text-danger">*</span></label>
                            <select name="leave_type_id" id="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                <option value="">Choose Leave Type</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" data-days="{{ $lt->total_days }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                        {{ $lt->name }} (Standard: {{ $lt->total_days }}d)
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Calendar Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', $currentYear) }}" min="2020" max="2050" required>
                            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-sm">Allocated Days <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" name="allocated_days" id="allocated_days" class="form-control @error('allocated_days') is-invalid @enderror" value="{{ old('allocated_days', '12') }}" min="0" max="365" required>
                        <div class="form-text text-xs">Remaining days will be recalculated automatically (Allocated - Used).</div>
                        @error('allocated_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.leave-allocations.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Grant Allocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('leave_type_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const days = selected.getAttribute('data-days');
        if (days) {
            document.getElementById('allocated_days').value = days;
        }
    });
</script>
@endpush
