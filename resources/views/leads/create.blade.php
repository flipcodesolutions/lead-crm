@extends('layouts.app')

@section('title', 'Create New Lead')
@section('page_title', 'Create New Lead')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <form action="{{ route('leads.store') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-person-plus-fill text-primary me-2"></i> Lead Information</h5>
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Leads
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <!-- Contact Details -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Customer / Contact Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Vikram Singhania" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Company Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g. Reliance Logistics Gujarat Pvt. Ltd." value="{{ old('company_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Mobile / Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. +91 98250 12345 or 9825012345" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Alternate Phone (Optional)</label>
                            <input type="text" name="alternate_phone" class="form-control" placeholder="e.g. +91 98250 98765" value="{{ old('alternate_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. contact@company.in" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Expected Deal Value ({{ \App\Models\Setting::get('currency_symbol', '₹') }})</label>
                            <input type="number" step="0.01" name="expected_value" class="form-control" placeholder="e.g. 150000.00" value="{{ old('expected_value', '0.00') }}">
                        </div>

                        <!-- Pipeline Categorization -->
                        <div class="col-12"><hr class="my-2 text-muted"></div>
                        <div class="col-12"><h6 class="fw-bold text-muted text-xs text-uppercase">Pipeline & Assignment</h6></div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Lead Source</label>
                            <select name="source_id" class="form-select">
                                <option value="">Select Source</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}" {{ old('source_id') == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Lead Status</label>
                            <select name="status_id" class="form-select">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Pipeline Stage</label>
                            <select name="stage_id" class="form-select">
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Assign To Sales Rep</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', auth()->id()) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role?->name ?? 'User' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Address Information -->
                        <div class="col-12"><hr class="my-2 text-muted"></div>
                        <div class="col-12"><h6 class="fw-bold text-muted text-xs text-uppercase">Location & Address</h6></div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-sm">Street Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Street address or office location" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">City</label>
                            <input type="text" name="city" class="form-control" placeholder="City" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">State / Province</label>
                            <input type="text" name="state" class="form-control" placeholder="State" value="{{ old('state') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Country</label>
                            <input type="text" name="country" class="form-control" placeholder="Country" value="{{ old('country') }}">
                        </div>

                        <!-- Description & Remarks -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Lead Description / Requirements</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter customer requirements, notes, or background context...">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-12 text-end pt-3">
                            <a href="{{ route('leads.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save & Create Lead
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
