@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'CRM Executive Dashboard')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<!-- KPI Metric Cards Grid -->
<div class="row g-3 mb-4">
    <!-- Total Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-primary-subtle text-primary me-3">
                    <i class="bi bi-funnel-fill fs-3"></i>
                </div>
                <div>
                    <span class="text-muted text-xs text-uppercase fw-semibold">Total Leads</span>
                    <h3 class="fw-bold mb-0">{{ number_format($totalLeads) }}</h3>
                    <span class="text-xs text-muted">{{ $newLeads }} new this cycle</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Qualified Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-info-subtle text-info me-3">
                    <i class="bi bi-check-circle-fill fs-3"></i>
                </div>
                <div>
                    <span class="text-muted text-xs text-uppercase fw-semibold">Qualified Leads</span>
                    <h3 class="fw-bold mb-0">{{ number_format($qualifiedLeads) }}</h3>
                    <span class="text-xs text-success"><i class="bi bi-graph-up-arrow"></i> {{ $totalLeads > 0 ? round(($qualifiedLeads/$totalLeads)*100) : 0 }}% qualification</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline Value -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-warning-subtle text-warning me-3">
                    <i class="bi bi-currency-rupee fs-3"></i>
                </div>
                <div>
                    <span class="text-muted text-xs text-uppercase fw-semibold">Pipeline Value</span>
                    <h3 class="fw-bold mb-0">@inr($pipelineValue)</h3>
                    <span class="text-xs text-muted">{{ $totalOpportunities }} active opportunities</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Won Deals & Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 p-3 bg-success-subtle text-success me-3">
                    <i class="bi bi-trophy-fill fs-3"></i>
                </div>
                <div>
                    <span class="text-muted text-xs text-uppercase fw-semibold">Won Revenue</span>
                    <h3 class="fw-bold mb-0 text-success">@inr($wonRevenue)</h3>
                    <span class="text-xs text-muted">{{ $wonOpportunities }} won deals ({{ $conversionRate }}% rate)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary KPI Row: Follow-up & Task Alerts -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white p-3" style="background: var(--primary-gradient) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-white-50 text-uppercase fw-semibold">Today's Follow-ups</div>
                    <h2 class="fw-bold my-1 text-white">{{ $todayFollowUpsCount }}</h2>
                    <a href="{{ route('follow-ups.index', ['tab' => 'today']) }}" class="text-white text-xs text-decoration-none">View today's schedule <i class="bi bi-arrow-right"></i></a>
                </div>
                <i class="bi bi-calendar-event fs-1 text-white-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 {{ $overdueFollowUpsCount > 0 ? 'bg-danger-subtle border-danger' : 'bg-light' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-semibold">Overdue Follow-ups</div>
                    <h2 class="fw-bold my-1 {{ $overdueFollowUpsCount > 0 ? 'text-danger' : 'text-dark' }}">{{ $overdueFollowUpsCount }}</h2>
                    <a href="{{ route('follow-ups.index', ['tab' => 'overdue']) }}" class="text-xs text-decoration-none {{ $overdueFollowUpsCount > 0 ? 'text-danger' : 'text-muted' }}">Action overdue items <i class="bi bi-arrow-right"></i></a>
                </div>
                <i class="bi bi-exclamation-octagon-fill fs-1 {{ $overdueFollowUpsCount > 0 ? 'text-danger' : 'text-muted' }}"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-semibold">Conversion Rate</div>
                    <h2 class="fw-bold my-1 text-primary">{{ $conversionRate }}%</h2>
                    <span class="text-xs text-muted">Leads to Won Deals</span>
                </div>
                <i class="bi bi-pie-chart-fill fs-1 text-primary"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Monthly Trend Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-graph-up text-primary me-2"></i> Lead Volume Trend (Last 6 Months)</span>
                <span class="badge bg-light text-muted border">Monthly</span>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="230"></canvas>
            </div>
        </div>
    </div>

    <!-- Pipeline Stage Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-pie-chart text-info me-2"></i> Leads by Pipeline Stage</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="max-height: 240px; width: 100%;">
                    <canvas id="stageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Grid: Today's Follow-ups & Leaderboard / Recent Leads -->
<div class="row g-4">
    <!-- Today's Follow-ups List -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-clock-history text-primary me-2"></i> Today's Follow-ups</span>
                <a href="{{ route('follow-ups.index', ['tab' => 'today']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($todaysFollowUps as $fu)
                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle bg-primary-subtle text-primary">
                                    <i class="bi bi-{{ $fu->type == 'Call' ? 'telephone' : ($fu->type == 'Meeting' ? 'people' : ($fu->type == 'WhatsApp' ? 'whatsapp' : 'chat')) }}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        <a href="{{ route('leads.show', $fu->lead_id) }}" class="text-decoration-none text-dark">{{ $fu->lead->name }}</a>
                                    </h6>
                                    <div class="text-muted text-xs">
                                        <i class="bi bi-clock me-1"></i> {{ $fu->follow_up_time ?? 'Anytime' }} &bull; {{ $fu->subject }}
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#completeFuModal{{ $fu->id }}">
                                <i class="bi bi-envelope me-1"></i> Send Mail
                            </button>
                        </div>

                        <!-- Complete & Send Mail Modal -->
                        <div class="modal fade" id="completeFuModal{{ $fu->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('follow-ups.complete', $fu->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-envelope-paper text-primary me-2"></i> Send Call Summary & Complete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-sm">Call / Interaction Outcome <span class="text-danger">*</span></label>
                                                <textarea name="result" class="form-control" rows="3" placeholder="Discussed requirements, customer requested quotation..." required>{{ $fu->description ?: $fu->subject }}</textarea>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-sm">Next Follow-up Date</label>
                                                    <input type="date" name="next_follow_up_date" class="form-control">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-sm">Time</label>
                                                    <input type="time" name="next_follow_up_time" class="form-control">
                                                </div>
                                            </div>

                                            <!-- Email Talk Summary Box -->
                                            <div class="p-3 bg-light rounded-3 border">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="send_email" value="1" id="dashSendEmailFu{{ $fu->id }}" checked>
                                                    <label class="form-check-label fw-semibold text-sm" for="dashSendEmailFu{{ $fu->id }}">
                                                        <i class="bi bi-envelope-check text-primary me-1"></i> Send Talk Summary in Email to Client
                                                    </label>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label text-xs text-muted mb-1">Recipient Email:</label>
                                                    <input type="email" name="recipient_email" class="form-control form-control-sm" value="{{ $fu->lead->email }}" placeholder="e.g. client@company.com" required>
                                                </div>
                                                <div>
                                                    <label class="form-label text-xs text-muted mb-1">Email Subject:</label>
                                                    <input type="text" name="email_subject" class="form-control form-control-sm" value="Call Summary & Discussion Notes - {{ $fu->lead->company_name ?: $fu->lead->name }} ({{ $fu->subject }})">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Send Mail & Complete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-check fs-2 d-block mb-2 text-muted"></i>
                            No pending follow-ups scheduled for today!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Leaderboard or Recent Activities -->
    <div class="col-lg-6">
        @if(!empty($salesLeaderboard) && count($salesLeaderboard) > 0)
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-trophy text-warning me-2"></i> Salesperson Performance Leaderboard</span>
                    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Full Report</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-xs text-uppercase">
                                <tr>
                                    <th>Sales Rep</th>
                                    <th>Role</th>
                                    <th>Leads</th>
                                    <th>Won Deals</th>
                                    <th class="text-end">Won Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesLeaderboard as $salesUser)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle bg-light text-dark text-xs">
                                                    {{ strtoupper(substr($salesUser->name, 0, 2)) }}
                                                </div>
                                                <span class="fw-semibold">{{ $salesUser->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-muted border">{{ $salesUser->role?->name ?? 'Rep' }}</span></td>
                                        <td>{{ $salesUser->total_leads }}</td>
                                        <td><span class="badge bg-success-subtle text-success">{{ $salesUser->won_deals }} Won</span></td>
                                        <td class="text-end fw-bold text-dark">@inr($salesUser->won_revenue ?? 0)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Recent Leads -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-fire text-danger me-2"></i> Recent Leads</span>
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-xs text-uppercase">
                                <tr>
                                    <th>Lead</th>
                                    <th>Stage</th>
                                    <th>Assigned</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLeads as $rLead)
                                    <tr>
                                        <td>
                                            <a href="{{ route('leads.show', $rLead->id) }}" class="fw-bold text-dark text-decoration-none">{{ $rLead->name }}</a>
                                            <div class="text-muted text-xs">@phone($rLead->phone)</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary badge-status">{{ $rLead->stage?->name ?? 'New' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-muted">{{ $rLead->assignedUser?->name ?? 'Unassigned' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('leads.show', $rLead->id) }}" class="btn btn-sm btn-light border rounded-circle"><i class="bi bi-chevron-right"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No recent leads found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyTrendLabels) !!},
            datasets: [{
                label: 'New Leads',
                data: {!! json_encode($monthlyTrendData) !!},
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                borderColor: '#4f46e5',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // 2. Stage Doughnut Chart
    const stageCtx = document.getElementById('stageChart').getContext('2d');
    new Chart(stageCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($stageLabels) !!},
            datasets: [{
                data: {!! json_encode($stageCounts) !!},
                backgroundColor: [
                    '#4f46e5', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            },
            cutout: '68%'
        }
    });
});
</script>
@endpush
