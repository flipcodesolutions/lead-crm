@extends('layouts.app')

@section('title', 'Quotation: ' . $quotation->quotation_number)
@section('page_title', 'Quotation Details')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<!-- Header Action Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border fs-6 px-3 py-2 rounded-pill">{{ $quotation->quotation_number }}</span>
                @php
                    $badgeClass = match($quotation->status) {
                        'Draft' => 'bg-secondary-subtle text-secondary',
                        'Sent' => 'bg-info-subtle text-info',
                        'Accepted' => 'bg-success-subtle text-success',
                        'Rejected' => 'bg-danger-subtle text-danger',
                        'Expired' => 'bg-warning-subtle text-warning',
                        default => 'bg-light text-dark'
                    };
                @endphp
                <span class="badge {{ $badgeClass }} fs-6 px-3 py-2 rounded-pill">Status: {{ $quotation->status }}</span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <!-- PDF Download -->
                <a href="{{ route('quotations.pdf', $quotation->id) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                </a>

                <!-- Print View -->
                <a href="{{ route('quotations.print', $quotation->id) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> Print
                </a>

                <!-- Send Email Modal -->
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#emailModal">
                    <i class="bi bi-envelope me-1"></i> Send Email
                </button>

                <!-- Edit -->
                <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>

                <!-- Status Actions -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Change Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 text-sm">
                        <li>
                            <form action="{{ route('quotations.status', $quotation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Sent">
                                <button type="submit" class="dropdown-item py-2 text-info"><i class="bi bi-send me-2"></i> Mark as Sent</button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quotations.status', $quotation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Accepted">
                                <button type="submit" class="dropdown-item py-2 text-success"><i class="bi bi-check-circle me-2"></i> Mark as Accepted (Won)</button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('quotations.status', $quotation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-x-circle me-2"></i> Mark as Rejected</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quotation Document Card (Paper Layout) -->
<div class="card border-0 shadow-sm mx-auto" style="max-width: 950px;">
    <div class="card-body p-5">
        <!-- Invoice Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold brand-font text-primary mb-1">{{ \App\Models\Setting::get('company_name', config('app.name', 'Odoo CRM')) }}</h3>
                <div class="text-xs text-muted">
                    <div>{{ \App\Models\Setting::get('company_address', '123 Business Avenue, Suite 500') }}</div>
                    <div>Email: {{ \App\Models\Setting::get('company_email', 'contact@company.com') }} &bull; Phone: {{ \App\Models\Setting::get('company_phone', '+1 555-0199') }}</div>
                    @if(\App\Models\Setting::get('tax_number'))
                        <div>Tax / VAT ID: {{ \App\Models\Setting::get('tax_number') }}</div>
                    @endif
                </div>
            </div>
            <div class="text-end">
                <h2 class="fw-bold text-uppercase text-dark tracking-wider mb-1" style="font-family: 'Outfit';">QUOTATION</h2>
                <div class="badge bg-light text-dark border fs-6 px-3 py-1 mb-2">{{ $quotation->quotation_number }}</div>
                <div class="text-xs text-muted"><strong>Date:</strong> {{ $quotation->quotation_date->format('M d, Y') }}</div>
                @if($quotation->valid_until)
                    <div class="text-xs text-muted"><strong>Valid Until:</strong> {{ $quotation->valid_until->format('M d, Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Bill To / Customer Details -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <span class="text-xs text-muted text-uppercase fw-semibold d-block mb-1">Quotation For:</span>
                <h5 class="fw-bold mb-1 text-dark">{{ $quotation->customer_name }}</h5>
                @if($quotation->customer_email)
                    <div class="text-sm text-muted"><i class="bi bi-envelope me-1"></i> {{ $quotation->customer_email }}</div>
                @endif
                @if($quotation->customer_phone)
                    <div class="text-sm text-muted"><i class="bi bi-telephone me-1"></i> {{ $quotation->customer_phone }}</div>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end">
                @if($quotation->opportunity)
                    <span class="text-xs text-muted text-uppercase fw-semibold d-block mb-1">Related Opportunity:</span>
                    <a href="{{ route('opportunities.show', $quotation->opportunity->id) }}" class="fw-semibold text-primary text-decoration-none">
                        {{ $quotation->opportunity->name }} ({{ $quotation->opportunity->opportunity_number }})
                    </a>
                @elseif($quotation->lead)
                    <span class="text-xs text-muted text-uppercase fw-semibold d-block mb-1">Related Lead:</span>
                    <a href="{{ route('leads.show', $quotation->lead->id) }}" class="fw-semibold text-primary text-decoration-none">
                        {{ $quotation->lead->name }} ({{ $quotation->lead->lead_number }})
                    </a>
                @endif
                <div class="text-xs text-muted mt-2"><strong>Prepared By:</strong> {{ $quotation->creator?->name ?? 'Sales Team' }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table align-middle">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 5%;">#</th>
                        <th style="width: 45%;">Item / Description</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 15%;">Unit Price</th>
                        <th class="text-center" style="width: 10%;">Tax</th>
                        <th class="text-end pe-3" style="width: 15%;">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $index => $item)
                        <tr>
                            <td class="ps-3 text-muted text-xs">{{ $index + 1 }}</td>
                            <td>
                                @if($item->service)
                                    <div class="fw-bold text-dark">{{ $item->service->name }}</div>
                                @endif
                                <div class="text-sm text-secondary">{{ $item->description }}</div>
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">@inr($item->price)</td>
                            <td class="text-center text-xs text-muted">{{ $item->tax_percentage }}%</td>
                            <td class="text-end pe-3 fw-bold text-dark">@inr($item->total)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Totals -->
        <div class="row justify-content-end mb-4">
            <div class="col-md-5">
                <div class="p-3 bg-light rounded-3">
                    <div class="d-flex justify-content-between text-sm py-1">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold text-dark">@inr($quotation->subtotal)</span>
                    </div>
                    @if($quotation->discount_amount > 0)
                        <div class="d-flex justify-content-between text-sm py-1">
                            <span class="text-muted">Discount:</span>
                            <span class="text-danger fw-semibold">-@inr($quotation->discount_amount)</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between text-sm py-1">
                        <span class="text-muted">GST / Tax Amount:</span>
                        <span class="text-dark fw-semibold">+@inr($quotation->tax_amount)</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <h5 class="fw-bold m-0 text-dark">Grand Total:</h5>
                        <h4 class="fw-bold m-0 text-primary">@inr($quotation->total_amount)</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes & Terms -->
        @if($quotation->notes || $quotation->terms_conditions)
            <div class="row g-4 pt-3 border-top text-xs text-muted">
                @if($quotation->notes)
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark text-xs text-uppercase mb-1">Customer Notes:</h6>
                        <p class="mb-0" style="white-space: pre-line;">{{ $quotation->notes }}</p>
                    </div>
                @endif
                @if($quotation->terms_conditions)
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark text-xs text-uppercase mb-1">Terms & Conditions:</h6>
                        <p class="mb-0" style="white-space: pre-line;">{{ $quotation->terms_conditions }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('quotations.email', $quotation->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Send Quotation via Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Recipient Email <span class="text-danger">*</span></label>
                        <input type="email" name="to_email" class="form-control" value="{{ $quotation->customer_email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="Quotation #{{ $quotation->quotation_number }} from {{ \App\Models\Setting::get('company_name', config('app.name')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Message Body</label>
                        <textarea name="message" class="form-control" rows="4" required>Dear {{ $quotation->customer_name }},

Please find attached our formal quotation #{{ $quotation->quotation_number }} for total amount of {{ \App\Helpers\CurrencyHelper::format($quotation->total_amount) }}.

Valid until: {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : '30 days' }}.

Best regards,
{{ auth()->user()->name }}
{{ \App\Models\Setting::get('company_name', config('app.name')) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Send Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
