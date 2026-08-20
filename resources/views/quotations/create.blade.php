@extends('layouts.app')

@section('title', 'Create Quotation')
@section('page_title', 'Create Quotation')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<form action="{{ route('quotations.store') }}" method="POST" id="quotationForm">
    @csrf
    @if(!empty($opportunity))
        <input type="hidden" name="opportunity_id" value="{{ $opportunity->id }}">
    @endif
    @if(!empty($lead))
        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
    @endif

    <div class="row g-4">
        <!-- Customer & General Information -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-receipt text-primary me-2"></i> Quotation Header & Customer</h5>
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Quotations
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Customer / Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $opportunity->customer_name ?? $lead->name ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Customer Email</label>
                            <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $lead->email ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Customer Phone</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $lead->phone ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" class="form-control" value="{{ old('quotation_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Valid Until Date</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', now()->addDays(30)->toDateString()) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-sm">Quotation Status</label>
                            <select name="status" class="form-select">
                                <option value="Draft" selected>Draft</option>
                                <option value="Sent">Sent</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Quotation Items Table -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i> Products & Line Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addItemRow()">
                        <i class="bi bi-plus-lg me-1"></i> Add Line Item
                    </button>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table align-middle" id="itemsTable">
                            <thead class="table-light text-xs text-uppercase">
                                <tr>
                                    <th style="width: 25%;">Service / Item</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 10%;">Qty</th>
                                    <th style="width: 12%;">Unit Price ({{ $currency }})</th>
                                    <th style="width: 10%;">Discount</th>
                                    <th style="width: 8%;">Tax (%)</th>
                                    <th style="width: 10%;" class="text-end">Line Total ({{ $currency }})</th>
                                    <th style="width: 5%;" class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                <!-- Row 0 -->
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][service_id]" class="form-select form-select-sm service-select" onchange="onServiceChange(this)">
                                            <option value="">-- Custom Item --</option>
                                            @foreach($services as $svc)
                                                <option value="{{ $svc->id }}" data-price="{{ $svc->price }}" data-tax="{{ $svc->tax_percentage }}" data-desc="{{ $svc->description }}">{{ $svc->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][description]" class="form-control form-control-sm item-desc" placeholder="Service description..." required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center" value="1" min="0.01" oninput="calculateTotals()" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][price]" class="form-control form-control-sm item-price text-end" value="0.00" min="0" oninput="calculateTotals()" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][discount]" class="form-control form-control-sm item-discount text-end" value="0.00" min="0" oninput="calculateTotals()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][tax_percentage]" class="form-control form-control-sm item-tax text-center" value="0.00" min="0" max="100" oninput="calculateTotals()">
                                    </td>
                                    <td class="text-end fw-bold item-total-display">
                                        {{ $currency }}0.00
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeItemRow(this)">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Totals Calculation Block -->
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-5">
                            <div class="p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between text-sm py-1">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="fw-semibold text-dark" id="displaySubtotal">{{ $currency }}0.00</span>
                                </div>
                                <div class="d-flex justify-content-between text-sm py-1">
                                    <span class="text-muted">Line Discounts:</span>
                                    <span class="text-danger fw-semibold" id="displayDiscount">-{{ $currency }}0.00</span>
                                </div>
                                <div class="d-flex justify-content-between text-sm py-1">
                                    <span class="text-muted">Total Tax:</span>
                                    <span class="text-dark fw-semibold" id="displayTax">+{{ $currency }}0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <h5 class="fw-bold m-0 text-dark">Grand Total:</h5>
                                    <h4 class="fw-bold m-0 text-primary" id="displayGrandTotal">{{ $currency }}0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes & Terms -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Customer Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Thank you for your business. Let us know if you have any questions.">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="3">{{ old('terms_conditions', \App\Models\Setting::get('terms_and_conditions', "1. Quotation validity: 30 days.\n2. Payment terms: 50% advance.")) }}</textarea>
                        </div>
                        <div class="col-12 text-end pt-3">
                            <a href="{{ route('quotations.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save & Generate Quotation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const servicesData = {!! json_encode($services) !!};
let rowIndex = 1;
const currencySymbol = "{{ $currency }}";

function onServiceChange(selectEl) {
    const row = selectEl.closest('.item-row');
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    
    if (selectedOption.value) {
        const price = selectedOption.getAttribute('data-price') || 0;
        const tax = selectedOption.getAttribute('data-tax') || 0;
        const desc = selectedOption.getAttribute('data-desc') || selectedOption.text;

        row.querySelector('.item-desc').value = desc;
        row.querySelector('.item-price').value = parseFloat(price).toFixed(2);
        row.querySelector('.item-tax').value = parseFloat(tax).toFixed(2);
    }
    calculateTotals();
}

function addItemRow() {
    const container = document.getElementById('itemsContainer');
    let serviceOptions = '<option value="">-- Custom Item --</option>';
    servicesData.forEach(svc => {
        serviceOptions += `<option value="${svc.id}" data-price="${svc.price}" data-tax="${svc.tax_percentage}" data-desc="${svc.description || svc.name}">${svc.name}</option>`;
    });

    const newRow = document.createElement('tr');
    newRow.className = 'item-row';
    newRow.innerHTML = `
        <td>
            <select name="items[${rowIndex}][service_id]" class="form-select form-select-sm service-select" onchange="onServiceChange(this)">
                ${serviceOptions}
            </select>
        </td>
        <td>
            <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm item-desc" placeholder="Service description..." required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-control form-control-sm item-qty text-center" value="1" min="0.01" oninput="calculateTotals()" required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control form-control-sm item-price text-end" value="0.00" min="0" oninput="calculateTotals()" required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${rowIndex}][discount]" class="form-control form-control-sm item-discount text-end" value="0.00" min="0" oninput="calculateTotals()">
        </td>
        <td>
            <input type="number" step="0.01" name="items[${rowIndex}][tax_percentage]" class="form-control form-control-sm item-tax text-center" value="0.00" min="0" max="100" oninput="calculateTotals()">
        </td>
        <td class="text-end fw-bold item-total-display">
            ${currencySymbol}0.00
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeItemRow(this)">
                <i class="bi bi-trash fs-6"></i>
            </button>
        </td>
    `;
    container.appendChild(newRow);
    rowIndex++;
    calculateTotals();
}

function removeItemRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    } else {
        alert('At least one item line is required.');
    }
}

function calculateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;
    let totalTax = 0;

    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
        const taxPercent = parseFloat(row.querySelector('.item-tax').value) || 0;

        const lineBase = qty * price;
        let lineSubtotal = lineBase - discount;
        if (lineSubtotal < 0) lineSubtotal = 0;

        const lineTax = (lineSubtotal * taxPercent) / 100;
        const lineTotal = lineSubtotal + lineTax;

        subtotal += lineBase;
        totalDiscount += discount;
        totalTax += lineTax;

        row.querySelector('.item-total-display').innerText = currencySymbol + lineTotal.toFixed(2);
    });

    const grandTotal = Math.max(0, (subtotal - totalDiscount) + totalTax);

    document.getElementById('displaySubtotal').innerText = currencySymbol + subtotal.toFixed(2);
    document.getElementById('displayDiscount').innerText = '-' + currencySymbol + totalDiscount.toFixed(2);
    document.getElementById('displayTax').innerText = '+' + currencySymbol + totalTax.toFixed(2);
    document.getElementById('displayGrandTotal').innerText = currencySymbol + grandTotal.toFixed(2);
}

document.addEventListener("DOMContentLoaded", function() {
    calculateTotals();
});
</script>
@endpush
