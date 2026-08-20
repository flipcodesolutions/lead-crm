@extends('layouts.app')

@section('title', 'Opportunity: ' . $opportunity->name)
@section('page_title', 'Opportunity Details')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<!-- Header Status & Action Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border fs-6 px-3 py-2 rounded-pill">{{ $opportunity->opportunity_number }}</span>
                <span class="badge {{ $opportunity->status == 'Won' ? 'bg-success' : ($opportunity->status == 'Lost' ? 'bg-danger' : 'bg-primary') }} fs-6 px-3 py-2 rounded-pill">
                    {{ $opportunity->status }}
                </span>
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill">
                    Stage: {{ $opportunity->stage?->name ?? 'N/A' }}
                </span>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('opportunities.edit', $opportunity->id) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-pencil me-1 text-primary"></i> Edit
                </a>

                @if($opportunity->status != 'Won')
                    <form action="{{ route('opportunities.mark-won', $opportunity->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="bi bi-trophy me-1"></i> Mark Won
                        </button>
                    </form>
                @endif

                @if($opportunity->status != 'Lost')
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#lostModal">
                        <i class="bi bi-x-circle me-1"></i> Mark Lost
                    </button>
                @endif

                <a href="{{ route('quotations.create', ['opportunity_id' => $opportunity->id, 'lead_id' => $opportunity->lead_id]) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-receipt me-1"></i> Create Quotation
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-2 text-dark">{{ $opportunity->name }}</h3>
                <h6 class="text-muted mb-4"><i class="bi bi-building me-1"></i> {{ $opportunity->company_name ?? $opportunity->customer_name }}</h6>

                <div class="row g-3 py-3 border-top border-bottom mb-3">
                    <div class="col-md-4">
                        <span class="text-xs text-muted text-uppercase fw-semibold d-block">Expected Revenue</span>
                        <h4 class="fw-bold text-primary mb-0">@inr($opportunity->expected_revenue)</h4>
                    </div>
                    <div class="col-md-4">
                        <span class="text-xs text-muted text-uppercase fw-semibold d-block">Win Probability</span>
                        <h4 class="fw-bold text-dark mb-0">{{ $opportunity->probability }}%</h4>
                    </div>
                    <div class="col-md-4">
                        <span class="text-xs text-muted text-uppercase fw-semibold d-block">Expected Close Date</span>
                        <h5 class="fw-bold text-dark mb-0">{{ $opportunity->expected_closing_date ? $opportunity->expected_closing_date->format('M d, Y') : 'Not set' }}</h5>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold text-sm text-dark">Scope & Description:</h6>
                    <p class="text-muted mb-0" style="white-space: pre-line;">{{ $opportunity->description ?? 'No scope details specified.' }}</p>
                </div>

                @if($opportunity->lost_reason)
                    <div class="alert alert-danger mb-0 mt-3">
                        <strong><i class="bi bi-exclamation-octagon me-1"></i> Lost Reason:</strong> {{ $opportunity->lost_reason }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Quotations Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0"><i class="bi bi-receipt text-primary me-2"></i> Linked Quotations</h5>
                <a href="{{ route('quotations.create', ['opportunity_id' => $opportunity->id, 'lead_id' => $opportunity->lead_id]) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> New Quotation
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Quotation #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($opportunity->quotations as $quote)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('quotations.show', $quote->id) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $quote->quotation_number }}
                                        </a>
                                    </td>
                                    <td>{{ $quote->quotation_date->format('M d, Y') }}</td>
                                    <td class="fw-bold text-dark">@inr($quote->total_amount)</td>
                                    <td>
                                        <span class="badge {{ $quote->status == 'Accepted' ? 'bg-success-subtle text-success' : ($quote->status == 'Sent' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary') }} badge-status">
                                            {{ $quote->status }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('quotations.pdf', $quote->id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5">
                                            <i class="bi bi-download"></i> PDF
                                        </a>
                                        <a href="{{ route('quotations.show', $quote->id) }}" class="btn btn-sm btn-primary rounded-pill px-2.5">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No quotations generated yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Metadata -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-muted text-xs text-uppercase mb-3">Customer Information</h6>
                <div class="mb-3">
                    <span class="text-xs text-muted d-block">Customer Name</span>
                    <span class="fw-bold text-dark">{{ $opportunity->customer_name }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-xs text-muted d-block">Company</span>
                    <span class="fw-semibold text-dark">{{ $opportunity->company_name ?? 'N/A' }}</span>
                </div>
                @if($opportunity->lead)
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <span class="text-xs text-muted d-block mb-1">Converted From Lead</span>
                        <a href="{{ route('leads.show', $opportunity->lead->id) }}" class="fw-bold text-primary text-decoration-none">
                            <i class="bi bi-funnel me-1"></i> {{ $opportunity->lead->name }} ({{ $opportunity->lead->lead_number }})
                        </a>
                    </div>
                @endif

                <hr>

                <h6 class="fw-bold text-muted text-xs text-uppercase mb-3">Sales Assignment</h6>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                    <div class="avatar-circle bg-primary text-white" style="width: 40px; height: 40px;">
                        {{ $opportunity->assignedUser ? strtoupper(substr($opportunity->assignedUser->name, 0, 2)) : '?' }}
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $opportunity->assignedUser->name ?? 'Unassigned' }}</div>
                        <span class="text-xs text-muted">{{ $opportunity->assignedUser->email ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Lost Modal -->
<div class="modal fade" id="lostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('opportunities.mark-lost', $opportunity->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">Mark Deal as Lost</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Reason for Deal Loss</label>
                        <textarea name="lost_reason" class="form-control" rows="3" placeholder="Provide lost reasons e.g. competitor pricing, budget cuts..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Confirm Mark Lost</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
