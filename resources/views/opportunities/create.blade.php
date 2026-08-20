@extends('layouts.app')

@section('title', 'New Opportunity')
@section('page_title', 'Create Opportunity')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '$');
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form action="{{ route('opportunities.store') }}" method="POST">
            @csrf
            @if(!empty($lead))
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-kanban text-primary me-2"></i> Opportunity Details</h5>
                    <a href="{{ route('opportunities.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Pipeline
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-sm">Opportunity / Deal Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Enterprise Software Deal" value="{{ old('name', !empty($lead) ? 'Opportunity: ' . $lead->name : '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $lead->name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $lead->company_name ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Expected Revenue ({{ $currency }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="expected_revenue" class="form-control" value="{{ old('expected_revenue', $lead->expected_value ?? '0.00') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Win Probability (%) <span class="text-danger">*</span></label>
                            <input type="number" name="probability" class="form-control" value="{{ old('probability', '50') }}" min="0" max="100" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Expected Closing Date</label>
                            <input type="date" name="expected_closing_date" class="form-control" value="{{ old('expected_closing_date', now()->addDays(14)->toDateString()) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Pipeline Stage <span class="text-danger">*</span></label>
                            <select name="stage_id" class="form-select" required>
                                @foreach($stages as $stg)
                                    <option value="{{ $stg->id }}" {{ old('stage_id', $lead->stage_id ?? '') == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Assign To Sales Rep</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('assigned_to', $lead->assigned_to ?? auth()->id()) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role?->name ?? 'User' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-sm">Description & Scope</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter deal scope, client requirements...">{{ old('description', $lead->description ?? '') }}</textarea>
                        </div>

                        <div class="col-12 text-end pt-3">
                            <a href="{{ route('opportunities.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save Opportunity
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
