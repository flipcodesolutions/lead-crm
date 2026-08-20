@extends('layouts.app')

@section('title', 'Quotations')
@section('page_title', 'Quotations & Invoicing')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <!-- Filter Bar -->
        <form action="{{ route('quotations.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search quotation #, customer, email..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent</option>
                    <option value="Accepted" {{ request('status') == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('quotations.index') }}" class="btn btn-light border"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>

            <div class="col-md-3 text-end ms-auto">
                <a href="{{ route('quotations.create') }}" class="btn btn-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Create Quotation
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quotations List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Quotation #</th>
                        <th>Customer</th>
                        <th>Quote Date</th>
                        <th>Valid Until</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('quotations.show', $quotation->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $quotation->quotation_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $quotation->customer_name }}</div>
                                @if($quotation->customer_email)
                                    <div class="text-xs text-muted">{{ $quotation->customer_email }}</div>
                                @endif
                            </td>
                            <td>{{ $quotation->quotation_date->format('M d, Y') }}</td>
                            <td>
                                @if($quotation->valid_until)
                                    <span class="text-xs {{ $quotation->valid_until->isPast() && $quotation->status != 'Accepted' ? 'text-danger' : 'text-muted' }}">
                                        {{ $quotation->valid_until->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark fs-6">@inr($quotation->total_amount)</td>
                            <td>
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
                                <span class="badge {{ $badgeClass }} badge-status">{{ $quotation->status }}</span>
                            </td>
                            <td>
                                <span class="text-xs">{{ $quotation->creator?->name ?? 'System' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('quotations.pdf', $quotation->id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5 me-1" title="Download PDF">
                                        <i class="bi bi-file-earmark-pdf text-danger"></i> PDF
                                    </a>
                                    <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-sm btn-primary rounded-pill px-2.5">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-muted"></i>
                                No quotations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($quotations->total() > 0)
                        Showing <strong>{{ $quotations->firstItem() }}</strong> to <strong>{{ $quotations->lastItem() }}</strong> of <strong>{{ $quotations->total() }}</strong> quotations
                    @else
                        Showing 0 quotations
                    @endif
                </div>

                <!-- Per Page Selector Dropdown -->
                <div class="d-flex align-items-center gap-2 ms-2">
                    <span class="text-muted text-xs">Show:</span>
                    <select class="form-select form-select-sm" style="width: auto; font-size: 0.8rem; padding: 0.25rem 0.6rem; border-radius: 0.375rem;" onchange="window.location.href = this.value">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                {{ $size }} per page
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($quotations->hasPages())
                <div>
                    {{ $quotations->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
