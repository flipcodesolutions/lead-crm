@extends('layouts.app')

@section('title', 'Lead: ' . $lead->name)
@section('page_title', 'Lead Details')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '₹');
@endphp

@section('content')
<!-- Odoo Stage Pipeline Tracker Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Stage Pipeline Progression -->
            <div class="stage-pipeline flex-grow-1">
                @foreach($allStages as $stg)
                    <form action="{{ route('leads.stage.update', $lead->id) }}" method="POST" class="d-inline m-0 flex-grow-1">
                        @csrf
                        <input type="hidden" name="stage_id" value="{{ $stg->id }}">
                        <button type="submit" class="stage-pipeline-step w-100 border-0 {{ $lead->stage_id == $stg->id ? 'active' : ($lead->stage && $stg->sort_order < $lead->stage->sort_order ? 'completed' : '') }}">
                            @if($lead->stage_id == $stg->id)
                                <i class="bi bi-check2-circle me-1"></i>
                            @endif
                            {{ $stg->name }}
                        </button>
                    </form>
                @endforeach
            </div>

            <!-- Current Status Badge -->
            <div>
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill">
                    <i class="bi bi-tag-fill me-1"></i> {{ $lead->leadStatus?->name ?? 'New' }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Odoo Quick Action Toolbar -->
<div class="card border-0 shadow-sm mb-4 bg-light">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Edit -->
            <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-white bg-white border shadow-sm rounded-pill px-3">
                <i class="bi bi-pencil me-1 text-primary"></i> Edit Lead
            </a>

            <!-- Assign / Reassign -->
            <button type="button" class="btn btn-sm btn-white bg-white border shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="bi bi-person-plus me-1 text-info"></i> {{ $lead->assigned_to ? 'Reassign Lead' : 'Assign Lead' }}
            </button>

            <!-- Schedule Follow-up -->
            <button type="button" class="btn btn-sm btn-white bg-white border shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#followUpModal">
                <i class="bi bi-calendar-plus me-1 text-warning"></i> Schedule Follow-up
            </button>

            <!-- Add Activity -->
            <button type="button" class="btn btn-sm btn-white bg-white border shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#activityModal">
                <i class="bi bi-check2-square me-1 text-secondary"></i> Add Activity
            </button>

            <!-- Qualify Action -->
            @if(!$lead->converted_at && (!str_contains(strtolower($lead->leadStatus?->name ?? ''), 'qualified')))
                <form action="{{ route('leads.qualify', $lead->id) }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Qualify Lead
                    </button>
                </form>
            @endif

            <!-- Convert to Opportunity -->
            @if(!$lead->opportunity)
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#convertModal">
                    <i class="bi bi-kanban me-1"></i> Convert to Opportunity
                </button>
            @else
                <a href="{{ route('opportunities.show', $lead->opportunity->id) }}" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-kanban me-1"></i> View Opportunity ({{ $lead->opportunity->opportunity_number }})
                </a>
            @endif

            <!-- Create Quotation -->
            <a href="{{ route('quotations.create', ['lead_id' => $lead->id, 'opportunity_id' => $lead->opportunity?->id]) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-receipt me-1"></i> Create Quotation
            </a>

            <!-- Mark Lost / Disqualify -->
            @if(!str_contains(strtolower($lead->leadStatus?->name ?? ''), 'lost'))
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-auto" data-bs-toggle="modal" data-bs-target="#lostModal">
                    <i class="bi bi-x-circle me-1"></i> Mark Lost
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Main Lead Header & Metrics Banner -->
<div class="row g-4 mb-4">
    <!-- Main Info Card -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-light text-dark border mb-2">{{ $lead->lead_number }}</span>
                        <h3 class="fw-bold mb-1">{{ $lead->name }}</h3>
                        @if($lead->company_name)
                            <h6 class="text-muted fw-normal"><i class="bi bi-building me-1"></i> {{ $lead->company_name }}</h6>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="text-xs text-muted text-uppercase fw-semibold">Expected Value</div>
                        <h3 class="fw-bold text-primary mb-0">@inr($lead->expected_value)</h3>
                    </div>
                </div>

                <div class="row g-3 pt-3 border-top">
                    <div class="col-sm-6 col-md-3">
                        <span class="text-xs text-muted d-block">Phone Number</span>
                        <a href="tel:{{ $lead->phone }}" class="text-decoration-none fw-semibold text-dark">
                            <i class="bi bi-telephone text-primary me-1"></i> @phone($lead->phone)
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-xs text-muted d-block">Email Address</span>
                        @if($lead->email)
                            <a href="mailto:{{ $lead->email }}" class="text-decoration-none fw-semibold text-dark text-truncate d-block">
                                <i class="bi bi-envelope text-primary me-1"></i> {{ $lead->email }}
                            </a>
                        @else
                            <span class="text-muted text-xs">Not specified</span>
                        @endif
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-xs text-muted d-block">Lead Source</span>
                        <span class="fw-semibold text-dark">
                            <i class="bi bi-signpost-2 text-primary me-1"></i> {{ $lead->source?->name ?? 'Direct' }}
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-xs text-muted d-block">Created By / Date</span>
                        <span class="fw-semibold text-dark text-xs d-block">
                            {{ $lead->creator?->name ?? 'System' }} &bull; {{ $lead->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Rep & Follow-up Alert Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold text-muted text-xs text-uppercase mb-3">Assignment & Next Step</h6>
                
                <!-- Assigned Rep -->
                <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3">
                    <div class="avatar-circle bg-primary text-white" style="width: 44px; height: 44px; background: var(--primary-gradient) !important;">
                        {{ $lead->assignedUser ? strtoupper(substr($lead->assignedUser->name, 0, 2)) : '?' }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-xs text-muted">Assigned Sales Rep</div>
                        <div class="fw-bold text-dark text-truncate">{{ $lead->assignedUser->name ?? 'Unassigned' }}</div>
                        <span class="text-xs text-muted">{{ $lead->assignedUser->role?->name ?? 'No rep assigned' }}</span>
                    </div>
                </div>

                <!-- Next Follow-up -->
                <div>
                    <span class="text-xs text-muted d-block mb-1">Next Follow-up Due</span>
                    @if($lead->next_follow_up_at)
                        <div class="p-2.5 rounded-3 d-flex align-items-center gap-2 {{ $lead->next_follow_up_at->isPast() ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }}">
                            <i class="bi bi-calendar-event fs-5"></i>
                            <div>
                                <div class="fw-bold text-sm">{{ $lead->next_follow_up_at->format('l, M d, Y') }}</div>
                                <div class="text-xs">{{ $lead->next_follow_up_at->format('g:i A') }} ({{ $lead->next_follow_up_at->diffForHumans() }})</div>
                            </div>
                        </div>
                    @else
                        <div class="p-2 bg-light rounded text-muted text-xs">
                            <i class="bi bi-info-circle me-1"></i> No upcoming follow-up scheduled.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Odoo Tabbed Information Section -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs border-0 px-3 pt-2" id="leadTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> Overview & Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="followups-tab" data-bs-toggle="tab" data-bs-target="#followups" type="button" role="tab">
                    <i class="bi bi-calendar-check me-1"></i> Follow-ups ({{ $lead->followUps->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities" type="button" role="tab">
                    <i class="bi bi-check2-square me-1"></i> Activities ({{ $lead->activities->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
                    <i class="bi bi-chat-left-text me-1"></i> Notes & Chatter ({{ $lead->notes->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="quotations-tab" data-bs-toggle="tab" data-bs-target="#quotations" type="button" role="tab">
                    <i class="bi bi-receipt me-1"></i> Quotations ({{ $lead->quotations->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Assignment History ({{ $lead->assignments->count() }})
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="leadTabsContent">
            
            <!-- Tab 1: Overview -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted text-xs text-uppercase mb-3">Address & Location</h6>
                        <div class="p-3 bg-light rounded-3">
                            <div class="mb-2"><strong>Street:</strong> {{ $lead->address ?? 'N/A' }}</div>
                            <div class="mb-2"><strong>City:</strong> {{ $lead->city ?? 'N/A' }}</div>
                            <div class="mb-2"><strong>State:</strong> {{ $lead->state ?? 'N/A' }}</div>
                            <div><strong>Country:</strong> {{ $lead->country ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted text-xs text-uppercase mb-3">Lead Requirements / Notes</h6>
                        <div class="p-3 bg-light rounded-3" style="min-height: 140px;">
                            {{ $lead->description ?? 'No detailed description provided.' }}
                        </div>
                    </div>
                    @if($lead->lost_reason)
                        <div class="col-12">
                            <div class="alert alert-danger mb-0">
                                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Lost Reason:</strong> {{ $lead->lost_reason }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab 2: Follow-ups -->
            <div class="tab-pane fade" id="followups" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0">Scheduled & Completed Follow-ups</h6>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#followUpModal">
                        <i class="bi bi-plus-lg me-1"></i> Schedule Follow-up
                    </button>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($lead->followUps as $fu)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-3">
                                    <div class="avatar-circle {{ $fu->status == 'Completed' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                                        <i class="bi bi-{{ $fu->type == 'Call' ? 'telephone' : ($fu->type == 'Meeting' ? 'people' : ($fu->type == 'WhatsApp' ? 'whatsapp' : 'chat')) }}"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0 text-dark">{{ $fu->subject }}</h6>
                                            <span class="badge bg-light text-dark border">{{ $fu->type }}</span>
                                            <span class="badge {{ $fu->status == 'Completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $fu->status }}</span>
                                        </div>
                                        <div class="text-xs text-muted my-1">
                                            <i class="bi bi-calendar me-1"></i> {{ $fu->follow_up_date->format('M d, Y') }} {{ $fu->follow_up_time ?? '' }} &bull; Logged by {{ $fu->user->name }}
                                        </div>
                                        @if($fu->description)
                                            <div class="text-sm text-secondary">{{ $fu->description }}</div>
                                        @endif
                                        @if($fu->result)
                                            <div class="mt-2 p-2 bg-light rounded text-xs">
                                                <strong>Outcome / Result:</strong> {{ $fu->result }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($fu->status == 'Pending')
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#completeFuModal{{ $fu->id }}">
                                            <i class="bi bi-envelope me-1"></i> Send Mail
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" data-bs-toggle="modal" data-bs-target="#emailSummaryLeadFuModal{{ $fu->id }}" title="Send Talk Summary via Email">
                                            <i class="bi bi-envelope me-1"></i> Resend Mail
                                        </button>
                                    @endif
                                </div>
                            </div>
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
                                                <textarea name="result" class="form-control" rows="3" placeholder="Enter what was talked about over the call..." required>{{ $fu->description ?: $fu->subject }}</textarea>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-sm">Next Follow-up Date (Optional)</label>
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
                                                    <input class="form-check-input" type="checkbox" name="send_email" value="1" id="sendEmailLeadFu{{ $fu->id }}" checked>
                                                    <label class="form-check-label fw-semibold text-sm" for="sendEmailLeadFu{{ $fu->id }}">
                                                        <i class="bi bi-envelope-check text-primary me-1"></i> Send Talk Summary in Email to Client
                                                    </label>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label text-xs text-muted mb-1">Recipient Email:</label>
                                                    <input type="email" name="recipient_email" class="form-control form-control-sm" value="{{ $lead->email }}" placeholder="e.g. client@company.com" required>
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

                        <!-- Email Summary Modal for Completed Calls -->
                        <div class="modal fade" id="emailSummaryLeadFuModal{{ $fu->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('follow-ups.email-summary', $fu->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-envelope text-primary me-2"></i> Send Call Summary Email</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-sm">Recipient Email <span class="text-danger">*</span></label>
                                                <input type="email" name="to_email" class="form-control" value="{{ $lead->email }}" placeholder="client@example.com" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-sm">Email Subject</label>
                                                <input type="text" name="subject" class="form-control" value="Call Summary & Discussion Notes - {{ $lead->company_name ?: $lead->name }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-sm">Discussion Summary / What was talked about <span class="text-danger">*</span></label>
                                                <textarea name="notes" class="form-control" rows="4" required>{{ $fu->result ?: $fu->description }}</textarea>
                                            </div>
                                            <div>
                                                <label class="form-label fw-semibold text-sm">Next Scheduled Date / Follow-up (Optional)</label>
                                                <input type="text" name="next_action_date" class="form-control" placeholder="e.g. Next Monday at 10:00 AM">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Send Email to Client</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No follow-ups recorded yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 3: Activities -->
            <div class="tab-pane fade" id="activities" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0">Task & Activity Tracker</h6>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#activityModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Activity
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Type</th>
                                <th>Title & Details</th>
                                <th>Due Date</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lead->activities as $act)
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $act->type }}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $act->title }}</div>
                                        <div class="text-muted text-xs">{{ $act->description }}</div>
                                    </td>
                                    <td>
                                        @if($act->due_date)
                                            <span class="text-xs {{ $act->due_date->isPast() && $act->status == 'Pending' ? 'text-danger fw-bold' : 'text-muted' }}">
                                                {{ $act->due_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-xs text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><span class="text-xs">{{ $act->user->name }}</span></td>
                                    <td>
                                        <span class="badge {{ $act->status == 'Completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $act->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($act->status == 'Pending')
                                            <form action="{{ route('activities.complete', $act->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5">
                                                    <i class="bi bi-check-lg"></i> Complete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No activities logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Notes / Chatter -->
            <div class="tab-pane fade" id="notes" role="tabpanel">
                <!-- Add Note Form -->
                <form action="{{ route('leads.notes.store', $lead->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="card border bg-light">
                        <div class="card-body p-3">
                            <label class="form-label fw-semibold text-sm">Post a Note / Internal Communication</label>
                            <textarea name="note" class="form-control mb-2" rows="2" placeholder="Write internal note, updates or meeting highlights..." required></textarea>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">
                                    <i class="bi bi-send me-1"></i> Post Note
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Notes Stream -->
                <div class="list-group list-group-flush">
                    @forelse($lead->notes as $n)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex gap-3">
                                <div class="avatar-circle bg-light text-dark">
                                    {{ strtoupper(substr($n->user->name, 0, 2)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark text-sm">{{ $n->user->name }}</span>
                                        <span class="text-xs text-muted">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-sm mt-1 text-secondary" style="white-space: pre-line;">{{ $n->note }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No notes posted yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Tab 5: Quotations -->
            <div class="tab-pane fade" id="quotations" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0">Quotations for this Lead</h6>
                    <a href="{{ route('quotations.create', ['lead_id' => $lead->id, 'opportunity_id' => $lead->opportunity?->id]) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="bi bi-plus-lg me-1"></i> Create Quotation
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light text-xs text-uppercase">
                            <tr>
                                <th>Quotation #</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lead->quotations as $quotation)
                                <tr>
                                    <td>
                                        <a href="{{ route('quotations.show', $quotation->id) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                    </td>
                                    <td>{{ $quotation->quotation_date->format('M d, Y') }}</td>
                                    <td class="fw-bold text-dark">{{ $currency }}{{ number_format($quotation->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $quotation->status == 'Accepted' ? 'bg-success-subtle text-success' : ($quotation->status == 'Sent' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary') }} badge-status">
                                            {{ $quotation->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('quotations.pdf', $quotation->id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5" title="Download PDF">
                                            <i class="bi bi-download"></i> PDF
                                        </a>
                                        <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-sm btn-primary rounded-pill px-2.5">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No quotations generated yet for this lead.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 6: Assignment History -->
            <div class="tab-pane fade" id="history" role="tabpanel">
                <div class="list-group list-group-flush">
                    @forelse($lead->assignments as $assignLog)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-sm">
                                        Assigned to <strong>{{ $assignLog->assignee->name }}</strong> by <strong>{{ $assignLog->assigner?->name ?? 'System' }}</strong>
                                    </span>
                                    @if($assignLog->remarks)
                                        <div class="text-xs text-muted mt-1"><i class="bi bi-chat-quote me-1"></i> {{ $assignLog->remarks }}</div>
                                    @endif
                                </div>
                                <span class="text-xs text-muted">{{ $assignLog->assigned_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No assignment logs available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- 1. Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('leads.assign', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assign Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Select Sales Representative</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Choose User...</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $lead->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role?->name ?? 'User' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Remarks / Handover Instructions</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Add any instructions for the assigned rep..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Schedule Follow-up Modal -->
<div class="modal fade" id="followUpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('leads.follow-ups.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Schedule Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="Call">Phone Call</option>
                                <option value="Meeting">Meeting / Demo</option>
                                <option value="Email">Email</option>
                                <option value="WhatsApp">WhatsApp</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g. Discuss proposal pricing" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Date</label>
                            <input type="date" name="follow_up_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Time</label>
                            <input type="time" name="follow_up_time" class="form-control" value="10:00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Notes / Agenda</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Key points to discuss..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Add Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('leads.activities.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Task / Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Activity Type</label>
                            <select name="type" class="form-select" required>
                                <option value="Call customer">Call customer</option>
                                <option value="Send quotation">Send quotation</option>
                                <option value="Schedule meeting">Schedule meeting</option>
                                <option value="Send proposal">Send proposal</option>
                                <option value="Follow up">Follow up</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Prepare custom quote for 50 licenses" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. Convert to Opportunity Modal -->
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('leads.convert', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Convert Lead to Opportunity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-sm">Opportunity Title</label>
                            <input type="text" name="name" class="form-control" value="Opportunity: {{ $lead->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Expected Revenue ({{ $currency }})</label>
                            <input type="number" step="0.01" name="expected_revenue" class="form-control" value="{{ $lead->expected_value }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Win Probability (%)</label>
                            <input type="number" name="probability" class="form-control" value="50" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Expected Closing Date</label>
                            <input type="date" name="expected_closing_date" class="form-control" value="{{ now()->addDays(14)->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Pipeline Stage</label>
                            <select name="stage_id" class="form-select">
                                @foreach($allStages as $stg)
                                    <option value="{{ $stg->id }}" {{ $lead->stage_id == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-kanban me-1"></i> Convert to Opportunity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5. Mark Lost Modal -->
<div class="modal fade" id="lostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('leads.mark-lost', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">Mark Lead as Lost / Disqualified</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm">Reason for Loss</label>
                        <textarea name="lost_reason" class="form-control" rows="3" placeholder="e.g. Budget constraints, opted for competitor, no longer interested..." required></textarea>
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
