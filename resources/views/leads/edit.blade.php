@extends('layouts.app')

@section('title', 'Edit Lead: ' . $lead->name)
@section('page_title', 'Edit Lead: ' . $lead->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <form action="{{ route('leads.update', $lead->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold m-0"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Lead #{{ $lead->lead_number }}</h5>
                        <span class="text-xs text-muted">Created {{ $lead->created_at->format('M d, Y') }}</span>
                    </div>
                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Detail
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <!-- Contact Details -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Customer / Contact Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $lead->company_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Alternate Phone</label>
                            <input type="text" name="alternate_phone" class="form-control" value="{{ old('alternate_phone', $lead->alternate_phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Expected Deal Value ({{ \App\Models\Setting::get('currency_symbol', '₹') }})</label>
                            <input type="number" step="0.01" name="expected_value" class="form-control" value="{{ old('expected_value', $lead->expected_value) }}">
                        </div>

                        <!-- Pipeline Categorization -->
                        <div class="col-12"><hr class="my-2 text-muted"></div>
                        <div class="col-12"><h6 class="fw-bold text-muted text-xs text-uppercase">Pipeline & Assignment</h6></div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Lead Source</label>
                            <select name="source_id" class="form-select">
                                <option value="">Select Source</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}" {{ old('source_id', $lead->source_id) == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Lead Status</label>
                            <select name="status_id" class="form-select">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('status_id', $lead->status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Pipeline Stage</label>
                            <select name="stage_id" class="form-select">
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ old('stage_id', $lead->stage_id) == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Assigned Rep</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $lead->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role?->name ?? 'User' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location & Address -->
                        <div class="col-12"><hr class="my-2 text-muted"></div>
                        <div class="col-12"><h6 class="fw-bold text-muted text-xs text-uppercase">Location & Address</h6></div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-sm">Street Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $lead->address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $lead->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $lead->country) }}">
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Lead Description / Notes</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $lead->description) }}</textarea>
                        </div>

                        <div class="col-12 text-end pt-3">
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Update Lead
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
