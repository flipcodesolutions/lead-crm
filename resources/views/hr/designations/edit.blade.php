@extends('layouts.app')

@section('title', 'Edit Designation')
@section('page_title', 'Edit Designation: ' . $designation->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Designation</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('hr.designations.update', $designation->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Designation Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $designation->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Job Description & Responsibilities</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $designation->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-sm">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $designation->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $designation->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('hr.designations.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Designation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
