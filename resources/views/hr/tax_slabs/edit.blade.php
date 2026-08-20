@extends('layouts.app')

@section('title', 'Edit Tax Slab')
@section('page_title', 'Edit Tax Slab: ' . $taxSlab->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Tax Slab</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.tax-slabs.update', $taxSlab->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Slab Name / Description <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $taxSlab->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Minimum Income (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="min_income" class="form-control @error('min_income') is-invalid @enderror" value="{{ old('min_income', $taxSlab->min_income) }}" required>
                            @error('min_income') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Maximum Income (₹)</label>
                            <input type="number" step="0.01" name="max_income" class="form-control @error('max_income') is-invalid @enderror" value="{{ old('max_income', $taxSlab->max_income) }}" placeholder="Leave blank if no upper limit">
                            @error('max_income') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Tax Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror" value="{{ old('tax_rate', $taxSlab->tax_rate) }}" min="0" max="100" required>
                            @error('tax_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Fixed Base Tax (₹)</label>
                            <input type="number" step="0.01" name="fixed_tax" class="form-control @error('fixed_tax') is-invalid @enderror" value="{{ old('fixed_tax', $taxSlab->fixed_tax) }}">
                            @error('fixed_tax') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-sm">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $taxSlab->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $taxSlab->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.tax-slabs.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Tax Slab</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
