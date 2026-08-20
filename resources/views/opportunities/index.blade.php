@extends('layouts.app')

@section('title', 'Sales Pipeline')
@section('page_title', 'Sales Pipeline')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<!-- Pipeline Header & Control Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <!-- Total Pipeline Summary -->
                <div>
                    <span class="text-xs text-muted text-uppercase fw-semibold">Total Pipeline Value</span>
                    <h4 class="fw-bold mb-0 text-primary">@inr($totalPipelineValue ?? 0)</h4>
                </div>
                <div class="vr mx-2"></div>
                <div>
                    <span class="text-xs text-muted text-uppercase fw-semibold">Opportunities</span>
                    <h5 class="fw-bold mb-0 text-dark">{{ $totalOpportunitiesCount ?? 0 }} Deals</h5>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- View Mode Toggle -->
                <div class="btn-group" role="group">
                    <a href="{{ route('opportunities.index', array_merge(request()->query(), ['view' => 'kanban'])) }}" class="btn btn-sm {{ ($viewMode ?? 'kanban') == 'kanban' ? 'btn-primary' : 'btn-light border' }}">
                        <i class="bi bi-kanban me-1"></i> Kanban
                    </a>
                    <a href="{{ route('opportunities.index', array_merge(request()->query(), ['view' => 'list'])) }}" class="btn btn-sm {{ ($viewMode ?? 'kanban') == 'list' ? 'btn-primary' : 'btn-light border' }}">
                        <i class="bi bi-list-ul me-1"></i> List
                    </a>
                </div>

                <a href="{{ route('opportunities.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> New Opportunity
                </a>
            </div>
        </div>
    </div>
</div>

@if(($viewMode ?? 'kanban') === 'kanban')
    <!-- ODOO KANBAN BOARD VIEW -->
    <div class="kanban-board">
        @foreach($kanbanData as $column)
            <div class="kanban-column" data-stage-id="{{ $column['stage']->id }}">
                <!-- Column Header -->
                <div class="kanban-column-header">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ $column['stage']->name }}</h6>
                        <span class="text-xs text-primary fw-semibold">@inr($column['total_value'])</span>
                    </div>
                    <span class="badge bg-white text-dark border rounded-pill px-2 py-1 text-xs">
                        {{ $column['count'] }}
                    </span>
                </div>

                <!-- Column Cards Container (Sortable) -->
                <div class="kanban-cards-container sortable-column" data-stage-id="{{ $column['stage']->id }}">
                    @foreach($column['opportunities'] as $opp)
                        <div class="kanban-card" data-opportunity-id="{{ $opp->id }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark border text-xs">{{ $opp->opportunity_number }}</span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 text-xs">
                                        <li><a class="dropdown-item py-1" href="{{ route('opportunities.show', $opp->id) }}"><i class="bi bi-eye me-1"></i> View Details</a></li>
                                        <li><a class="dropdown-item py-1" href="{{ route('quotations.create', ['opportunity_id' => $opp->id]) }}"><i class="bi bi-receipt me-1"></i> Create Quotation</a></li>
                                        @if($opp->status != 'Won')
                                            <li>
                                                <form action="{{ route('opportunities.mark-won', $opp->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item py-1 text-success"><i class="bi bi-trophy me-1"></i> Mark Won</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-1 text-dark">
                                <a href="{{ route('opportunities.show', $opp->id) }}" class="text-decoration-none text-dark">{{ $opp->name }}</a>
                            </h6>
                            <div class="text-xs text-muted mb-2">
                                <i class="bi bi-person me-1"></i> {{ $opp->customer_name }}
                                @if($opp->company_name) &bull; {{ $opp->company_name }} @endif
                            </div>

                            <!-- Probability progress bar -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between text-xs mb-1">
                                    <span class="text-muted">Win Probability</span>
                                    <span class="fw-semibold">{{ $opp->probability }}%</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar {{ $opp->probability >= 70 ? 'bg-success' : ($opp->probability >= 40 ? 'bg-primary' : 'bg-warning') }}" style="width: {{ $opp->probability }}%"></div>
                                </div>
                            </div>

                            <!-- Footer of Card -->
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold text-primary text-sm">@inr($opp->expected_revenue)</span>
                                <div class="avatar-circle bg-light text-dark" style="width: 26px; height: 26px; font-size: 0.65rem;" title="Assigned to {{ $opp->assignedUser->name ?? 'Unassigned' }}">
                                    {{ strtoupper(substr($opp->assignedUser->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@else
    <!-- LIST VIEW MODE -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-xs text-uppercase">
                        <tr>
                            <th class="ps-4">Deal / Opportunity</th>
                            <th>Customer & Company</th>
                            <th>Stage</th>
                            <th>Revenue</th>
                            <th>Probability</th>
                            <th>Assigned To</th>
                            <th>Closing Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opportunities as $opp)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('opportunities.show', $opp->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $opp->name }}
                                    </a>
                                    <div class="text-xs text-muted">{{ $opp->opportunity_number }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-sm">{{ $opp->customer_name }}</div>
                                    <div class="text-xs text-muted">{{ $opp->company_name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary badge-status">{{ $opp->stage?->name ?? 'N/A' }}</span>
                                </td>
                                <td class="fw-bold text-dark">@inr($opp->expected_revenue)</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2" style="width: 100px;">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $opp->probability }}%"></div>
                                        </div>
                                        <span class="text-xs fw-semibold">{{ $opp->probability }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs">{{ $opp->assignedUser?->name ?? 'Unassigned' }}</span>
                                </td>
                                <td>
                                    <span class="text-xs text-muted">{{ $opp->expected_closing_date ? $opp->expected_closing_date->format('M d, Y') : '—' }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('opportunities.show', $opp->id) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No opportunities found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination & Per-Page Footer -->
            <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted text-xs">
                        @if($opportunities->total() > 0)
                            Showing <strong>{{ $opportunities->firstItem() }}</strong> to <strong>{{ $opportunities->lastItem() }}</strong> of <strong>{{ $opportunities->total() }}</strong> deals
                        @else
                            Showing 0 deals
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

                @if($opportunities->hasPages())
                    <div>
                        {{ $opportunities->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const columns = document.querySelectorAll('.sortable-column');
    
    columns.forEach(col => {
        new Sortable(col, {
            group: 'pipeline',
            animation: 180,
            ghostClass: 'bg-primary-subtle',
            onEnd: function(evt) {
                const itemEl = evt.item;
                const targetColumn = evt.to;
                const opportunityId = itemEl.getAttribute('data-opportunity-id');
                const newStageId = targetColumn.getAttribute('data-stage-id');

                if (opportunityId && newStageId) {
                    // Send AJAX request to update stage
                    fetch(`/opportunities/${opportunityId}/stage-ajax`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ stage_id: newStageId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Stage updated successfully');
                        }
                    })
                    .catch(err => console.error('Error updating stage:', err));
                }
            }
        });
    });
});
</script>
@endpush
