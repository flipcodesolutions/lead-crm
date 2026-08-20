@extends('layouts.app')

@section('title', 'CRM Settings')
@section('page_title', 'System & Branding Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <!-- Company & Branding -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold m-0"><i class="bi bi-building-gear text-primary me-2"></i> Company & Quotation Branding</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Company / Enterprise Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings['company_name']) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Official Email</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $settings['company_email']) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Phone Number</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" placeholder="$ or ₹ or €" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Tax / GST / VAT ID</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $settings['tax_number']) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Lead ID Prefix</label>
                            <input type="text" name="lead_prefix" class="form-control" value="{{ old('lead_prefix', $settings['lead_prefix']) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Quotation ID Prefix</label>
                            <input type="text" name="quotation_prefix" class="form-control" value="{{ old('quotation_prefix', $settings['quotation_prefix']) }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-sm">Company Office Address</label>
                            <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $settings['company_address']) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotation Standard Terms -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold m-0"><i class="bi bi-file-earmark-text text-primary me-2"></i> Default Terms & Conditions</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Terms & Conditions (pre-populated on new quotations)</label>
                        <textarea name="terms_and_conditions" class="form-control" rows="4">{{ old('terms_and_conditions', $settings['terms_and_conditions']) }}</textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save All CRM Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
