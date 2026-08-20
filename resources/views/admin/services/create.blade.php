@extends('layouts.app')

@section('title', 'Add Product / Service')
@section('page_title', 'Create Catalog Item')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '$');
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <form action="{{ route('admin.services.store') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i> Product / Service Item</h5>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Product / Service Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. CRM Enterprise Annual License" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Base Price ({{ $currency }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" value="{{ old('price', '0.00') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Default Tax Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="tax_percentage" class="form-control" placeholder="0.00" value="{{ old('tax_percentage', '0.00') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Item specifications or default quotation line description...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12 text-end pt-2">
                            <a href="{{ route('admin.services.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Product / Service</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
