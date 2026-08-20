@extends('layouts.app')

@section('title', 'Follow-up Center')
@section('page_title', 'Follow-up Activities')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a href="{{ route('follow-ups.index', ['tab' => 'today']) }}" class="nav-link rounded-pill px-3 py-1.5 {{ $tab == 'today' ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-1"></i> Today
                        <span class="badge bg-white text-dark rounded-pill ms-1">{{ $todayCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('follow-ups.index', ['tab' => 'overdue']) }}" class="nav-link rounded-pill px-3 py-1.5 {{ $tab == 'overdue' ? 'active' : '' }}">
                        <i class="bi bi-exclamation-octagon me-1"></i> Overdue
                        <span class="badge {{ $overdueCount > 0 ? 'bg-danger text-white' : 'bg-white text-dark' }} rounded-pill ms-1">{{ $overdueCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('follow-ups.index', ['tab' => 'upcoming']) }}" class="nav-link rounded-pill px-3 py-1.5 {{ $tab == 'upcoming' ? 'active' : '' }}">
                        <i class="bi bi-calendar-event me-1"></i> Upcoming
                        <span class="badge bg-white text-dark rounded-pill ms-1">{{ $upcomingCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('follow-ups.index', ['tab' => 'completed']) }}" class="nav-link rounded-pill px-3 py-1.5 {{ $tab == 'completed' ? 'active' : '' }}">
                        <i class="bi bi-check-circle me-1"></i> Completed
                        <span class="badge bg-white text-dark rounded-pill ms-1">{{ $completedCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('follow-ups.index', ['tab' => 'all']) }}" class="nav-link rounded-pill px-3 py-1.5 {{ $tab == 'all' ? 'active' : '' }}">
                        All ({{ $allCount }})
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Follow-ups List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Type</th>
                        <th>Subject & Lead</th>
                        <th>Scheduled Date</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Outcome / Notes</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($followUps as $fu)
                        <tr>
                            <td class="ps-4">
                                <div class="avatar-circle {{ $fu->status == 'Completed' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">
                                    <i class="bi bi-{{ $fu->type == 'Call' ? 'telephone' : ($fu->type == 'Meeting' ? 'people' : ($fu->type == 'WhatsApp' ? 'whatsapp' : 'chat')) }}"></i>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $fu->subject }}</div>
                                <div class="text-xs text-muted">
                                    <i class="bi bi-person me-1"></i>
                                    <a href="{{ route('leads.show', $fu->lead_id) }}" class="text-decoration-none text-primary">
                                        {{ $fu->lead->name }} ({{ $fu->lead->lead_number }})
                                    </a>
                                    @if($fu->lead->phone) &bull; {{ $fu->lead->phone }} @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-sm fw-medium {{ $fu->follow_up_date->isPast() && $fu->status == 'Pending' ? 'text-danger fw-bold' : 'text-dark' }}">
                                    {{ $fu->follow_up_date->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-muted">{{ $fu->follow_up_time ?? 'Anytime' }}</div>
                            </td>
                            <td>
                                <span class="text-xs">{{ $fu->user->name }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $fu->status == 'Completed' ? 'bg-success-subtle text-success' : ($fu->follow_up_date->isPast() ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }} badge-status">
                                    {{ $fu->status == 'Pending' && $fu->follow_up_date->isPast() ? 'Overdue' : $fu->status }}
                                </span>
                            </td>
                            <td>
                                @if($fu->result)
                                    <div class="text-xs text-secondary text-truncate" style="max-width: 200px;" title="{{ $fu->result }}">
                                        <strong>Result:</strong> {{ $fu->result }}
                                    </div>
                                @elseif($fu->description)
                                    <div class="text-xs text-muted text-truncate" style="max-width: 200px;">{{ $fu->description }}</div>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($fu->status == 'Pending')
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#completeFuModal{{ $fu->id }}">
                                        <i class="bi bi-envelope me-1"></i> Send Mail
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 me-1" data-bs-toggle="modal" data-bs-target="#emailSummaryModal{{ $fu->id }}" title="Send Talk Summary via Email">
                                        <i class="bi bi-envelope me-1"></i> Resend Mail
                                    </button>
                                @endif
                                <a href="{{ route('leads.show', $fu->lead_id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5">
                                    View Lead
                                </a>
                            </td>
                        </tr>

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
                                                <textarea name="result" class="form-control" rows="3" placeholder="Enter conversation notes, client feedback, discussion outcome..." required>{{ $fu->description ?: $fu->subject }}</textarea>
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
                                                    <input class="form-check-input" type="checkbox" name="send_email" value="1" id="sendEmailFu{{ $fu->id }}" checked>
                                                    <label class="form-check-label fw-semibold text-sm" for="sendEmailFu{{ $fu->id }}">
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

                        <!-- Email Summary Modal for Completed Calls -->
                        <div class="modal fade" id="emailSummaryModal{{ $fu->id }}" tabindex="-1">
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
                                                <input type="email" name="to_email" class="form-control" value="{{ $fu->lead->email }}" placeholder="client@example.com" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-sm">Email Subject</label>
                                                <input type="text" name="subject" class="form-control" value="Call Summary & Discussion Notes - {{ $fu->lead->company_name ?: $fu->lead->name }}">
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
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-1 d-block mb-2 text-muted"></i>
                                No follow-ups found in this view.
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
                    @if($followUps->total() > 0)
                        Showing <strong>{{ $followUps->firstItem() }}</strong> to <strong>{{ $followUps->lastItem() }}</strong> of <strong>{{ $followUps->total() }}</strong> follow-ups
                    @else
                        Showing 0 follow-ups
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

            @if($followUps->hasPages())
                <div>
                    {{ $followUps->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
