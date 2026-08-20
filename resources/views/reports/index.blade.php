@extends('layouts.app')

@section('title', 'Analytics & Reports')
@section('page_title', 'Analytics & Reports')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<!-- Date Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label text-xs fw-bold text-muted mb-1">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-xs fw-bold text-muted mb-1">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3 pt-3">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">
                    <i class="bi bi-filter me-1"></i> Apply Filter
                </button>
            </div>
            <div class="col-md-3 text-end pt-3 ms-auto">
                <a href="{{ route('reports.export.leads') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Leads CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Performance KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Leads Generated</span>
            <h3 class="fw-bold my-1 text-primary">{{ number_format($leadsCount) }}</h3>
            <span class="text-xs text-muted">In selected period</span>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Opportunities Created</span>
            <h3 class="fw-bold my-1 text-info">{{ number_format($oppsCount) }}</h3>
            <span class="text-xs text-muted">Pipeline entries</span>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Deals Won</span>
            <h3 class="fw-bold my-1 text-success">{{ number_format($wonDeals) }}</h3>
            <span class="text-xs text-success"><i class="bi bi-trophy"></i> Successful closes</span>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-xs text-muted text-uppercase fw-semibold">Won Revenue</span>
            <h3 class="fw-bold my-1 text-success">@inr($wonRevenue)</h3>
            <span class="text-xs text-muted">Quotations total: @inr($quotationsTotal)</span>
        </div>
    </div>
</div>

<!-- Salesperson Performance Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4">
        <h5 class="fw-bold m-0"><i class="bi bi-people-fill text-primary me-2"></i> Sales Representative Performance</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Sales Representative</th>
                        <th>Role</th>
                        <th class="text-center">Assigned Leads</th>
                        <th class="text-center">Opportunities</th>
                        <th class="text-center">Completed Follow-ups</th>
                        <th class="text-center">Won Deals</th>
                        <th class="text-end pe-4">Won Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesUsers as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle bg-light text-dark">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="text-xs text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $user->role?->name ?? 'Rep' }}</span></td>
                            <td class="text-center fw-semibold">{{ $user->assigned_leads }}</td>
                            <td class="text-center fw-semibold">{{ $user->total_opportunities }}</td>
                            <td class="text-center">{{ $user->completed_followups }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success badge-status">{{ $user->won_deals }} Deals</span>
                            </td>
                            <td class="text-end pe-4 fw-bold text-dark fs-6">@inr($user->won_revenue ?? 0)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No sales user activity recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Lead Source Performance -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-signpost-2 text-primary me-2"></i> Lead Sources Breakdown</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Source Channel</th>
                                <th class="text-center">Leads Acquired</th>
                                <th class="text-end pe-4">Share (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sources as $source)
                                @php
                                    $pct = $leadsCount > 0 ? round(($source->leads_count / $leadsCount) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">{{ $source->name }}</td>
                                    <td class="text-center">{{ $source->leads_count }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress flex-grow-1" style="max-width: 80px; height: 6px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs fw-semibold">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No sources found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stage Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-layers text-primary me-2"></i> Stage Funnel Distribution</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Pipeline Stage</th>
                                <th class="text-center">Leads</th>
                                <th class="text-center">Opportunities</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stages as $stage)
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">
                                        <span class="badge bg-primary-subtle text-primary badge-status me-1">{{ $stage->sort_order }}</span>
                                        {{ $stage->name }}
                                    </td>
                                    <td class="text-center fw-semibold">{{ $stage->leads_count }}</td>
                                    <td class="text-center fw-semibold text-info">{{ $stage->opportunities_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No stages found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
