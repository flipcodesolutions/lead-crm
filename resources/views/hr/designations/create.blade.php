@extends('layouts.app')

@section('title', 'Add Designation')
@section('page_title', 'Create Job Designation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-person-badge-fill text-primary me-2"></i> Designation Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.designations.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Designation Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Senior Sales Executive, HR Manager, Software Engineer" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Job Description & Responsibilities</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Core duties, hierarchy rank, and expected role deliverables...">{{ old('description') }}</textarea>
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
                        <a href="{{ route('hr.designations.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Designation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
