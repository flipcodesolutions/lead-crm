@extends('layouts.app')

@section('title', 'Create Role')
@section('page_title', 'Create Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-shield-plus text-primary me-2"></i> New Role</h5>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Roles
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Telecaller, Support" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of role responsibilities...">{{ old('description') }}</textarea>
                    </div>
                    <div class="text-end pt-2">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Role</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
