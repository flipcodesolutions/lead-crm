@extends('layouts.app')

@section('title', 'Add Lead Status')
@section('page_title', 'Create Lead Status')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <form action="{{ route('admin.lead-statuses.store') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-tag-fill text-primary me-2"></i> New Lead Status</h5>
                    <a href="{{ route('admin.lead-statuses.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Status Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. New, Qualified, Lost" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="text-end pt-2">
                        <a href="{{ route('admin.lead-statuses.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Status</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
