@extends('layouts.app')

@section('title', 'Add Leave Type')
@section('page_title', 'Create Leave Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-calendar-plus text-primary me-2"></i> Leave Type Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.leave-types.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Leave Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Casual Leave (CL), Sick Leave (SL), Earned/Privilege Leave (PL)" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Standard Annual Quota (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="total_days" class="form-control @error('total_days') is-invalid @enderror" value="{{ old('total_days', '12') }}" min="0" max="365" required>
                            @error('total_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Paid or Unpaid <span class="text-danger">*</span></label>
                            <select name="is_paid" class="form-select @error('is_paid') is-invalid @enderror" required>
                                <option value="1" {{ old('is_paid', '1') == '1' ? 'selected' : '' }}>Paid Leave</option>
                                <option value="0" {{ old('is_paid') == '0' ? 'selected' : '' }}>Unpaid Leave</option>
                            </select>
                            @error('is_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Description & Policies</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Explain carry forward rules, notice period, or maximum consecutive days...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-sm">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.leave-types.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Leave Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
