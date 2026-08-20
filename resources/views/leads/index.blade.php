@extends('layouts.app')

@section('title', 'Leads Management')
@section('page_title', 'Leads Management')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <!-- Filter Bar -->
        <form action="{{ route('leads.index') }}" method="GET" class="row g-2 align-items-center">
            <!-- Search -->
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search name, phone, email, #" value="{{ request('search') }}">
                </div>
            </div>

            <!-- Stage Filter -->
            <div class="col-md-2">
                <select name="stage_id" class="form-select">
                    <option value="">All Stages</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <select name="status_id" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Source Filter -->
            <div class="col-md-2">
                <select name="source_id" class="form-select">
                    <option value="">All Sources</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" {{ request('source_id') == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Assignee Filter (if Admin/Manager) -->
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <div class="col-md-2">
                <select name="assigned_to" class="form-select">
                    <option value="">All Assignees</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Submit & Reset Buttons -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 px-2" title="Filter"><i class="bi bi-funnel"></i></button>
                @if(request()->hasAny(['search', 'stage_id', 'status_id', 'source_id', 'assigned_to']))
                    <a href="{{ route('leads.index') }}" class="btn btn-light border px-2" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Leads Data Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-list-stars text-primary me-1"></i> All Leads</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $leads->total() }} Total</span>
        </div>
        <div class="d-flex gap-2">
            @if(in_array(auth()->user()->role?->name, ['Admin', 'Manager']))
                <a href="{{ route('leads.import') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
                </a>
            @endif
            <a href="{{ route('reports.export.leads') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Create Lead
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Lead Info</th>
                        <th>Contact</th>
                        <th>Source</th>
                        <th>Stage & Status</th>
                        <th>Value</th>
                        <th>Assigned To</th>
                        <th>Next Follow-up</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('leads.show', $lead->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $lead->name }}
                                </a>
                                <div class="text-xs text-muted">
                                    <span class="badge bg-light text-dark border">{{ $lead->lead_number }}</span>
                                    @if($lead->company_name) &bull; {{ $lead->company_name }} @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-sm fw-medium"><i class="bi bi-telephone text-muted me-1"></i> @phone($lead->phone)</div>
                                @if($lead->email)
                                    <div class="text-xs text-muted"><i class="bi bi-envelope text-muted me-1"></i> {{ $lead->email }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $lead->source?->name ?? 'Direct' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary badge-status">{{ $lead->stage?->name ?? 'New' }}</span>
                                @if($lead->leadStatus)
                                    <span class="badge bg-info-subtle text-info badge-status">{{ $lead->leadStatus->name }}</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">
                                @inr($lead->expected_value)
                            </td>
                            <td>
                                @if($lead->assignedUser)
                                    <div class="d-flex align-items-center gap-1.5">
                                        <div class="avatar-circle bg-light text-dark" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                            {{ strtoupper(substr($lead->assignedUser->name, 0, 2)) }}
                                        </div>
                                        <span class="text-xs fw-medium">{{ $lead->assignedUser->name }}</span>
                                    </div>
                                @else
                                    <span class="badge bg-warning-subtle text-warning badge-status">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($lead->next_follow_up_at)
                                    <span class="text-xs {{ $lead->next_follow_up_at->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                        <i class="bi bi-calendar-event me-1"></i> {{ $lead->next_follow_up_at->format('M d, g:i A') }}
                                    </span>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5 me-1" title="View Lead Detail">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light border rounded-circle dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                        <li><a class="dropdown-item py-2" href="{{ route('leads.edit', $lead->id) }}"><i class="bi bi-pencil me-2 text-primary"></i> Edit Lead</a></li>
                                        <li><a class="dropdown-item py-2" href="{{ route('opportunities.create', ['lead_id' => $lead->id]) }}"><i class="bi bi-kanban me-2 text-info"></i> Convert to Opportunity</a></li>
                                        <li><a class="dropdown-item py-2" href="{{ route('quotations.create', ['lead_id' => $lead->id]) }}"><i class="bi bi-receipt me-2 text-success"></i> Create Quotation</a></li>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                No leads found matching your criteria.
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
                    @if($leads->total() > 0)
                        Showing <strong>{{ $leads->firstItem() }}</strong> to <strong>{{ $leads->lastItem() }}</strong> of <strong>{{ $leads->total() }}</strong> leads
                    @else
                        Showing 0 leads
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

            @if($leads->hasPages())
                <div>
                    {{ $leads->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
