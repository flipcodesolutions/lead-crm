@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page_title', 'Employee Leave Requests')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.leave-requests.index') }}" method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="leave_type_id" class="form-select">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['status', 'employee_id', 'leave_type_id', 'date_from', 'date_to']))
                    <a href="{{ route('hr.leave-requests.index') }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-calendar-check text-primary me-1"></i> Leave Applications</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $leaveRequests->total() }} Applications</span>
        </div>
        <a href="{{ route('hr.leave-requests.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Apply for Leave
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Leave Type</th>
                        <th>Leave Dates</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $req)
                        @php
                            $badgeClass = match($req->status) {
                                'Approved' => 'bg-success-subtle text-success',
                                'Pending' => 'bg-warning-subtle text-warning',
                                'Rejected' => 'bg-danger-subtle text-danger',
                                'Cancelled' => 'bg-secondary-subtle text-secondary',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('hr.employees.show', $req->employee_id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $req->employee->full_name }}
                                </a>
                                <div class="text-xs text-muted">{{ $req->employee->department->name ?? 'Staff' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info">{{ $req->leaveType->name }}</span>
                            </td>
                            <td class="text-xs">
                                <div class="fw-semibold text-dark">{{ $req->from_date->format('d M Y') }} &mdash; {{ $req->to_date->format('d M Y') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $req->total_days }} day(s)</span>
                            </td>
                            <td class="text-xs text-muted text-truncate" style="max-width: 180px;" title="{{ $req->reason }}">
                                {{ $req->reason }}
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }} badge-status">{{ $req->status }}</span>
                            </td>
                            <td class="text-xs text-muted">
                                @if($req->approver)
                                    <div class="fw-semibold text-dark">{{ $req->approver->name }}</div>
                                    <span class="text-muted">{{ $req->approved_at?->format('d M, h:i A') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('hr.leave-requests.show', $req->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Review Request">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if(auth()->user()->canManageHR())
                                        @if($req->status === 'Pending')
                                            <form action="{{ route('hr.leave-requests.approve', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5" title="Approve Request" onclick="return confirm('Approve this leave application?');">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Reject Request" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('hr.leave-requests.reject', $req->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-content text-start">
                                                            <div class="modal-header">
                                                                <h6 class="modal-title fw-bold">Reject Leave Request</h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="text-sm text-muted">Please provide a reason for rejecting {{ $req->employee->full_name }}'s leave request:</p>
                                                                <textarea name="rejected_reason" class="form-control" rows="3" placeholder="e.g. Critical deployment scheduled, insufficient coverage..." required></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    @if($req->status !== 'Cancelled')
                                        <form action="{{ route('hr.leave-requests.cancel', $req->id) }}" method="POST" onsubmit="return confirm('Cancel this leave request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Cancel Request">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No leave requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($leaveRequests->total() > 0)
                        Showing <strong>{{ $leaveRequests->firstItem() }}</strong> to <strong>{{ $leaveRequests->lastItem() }}</strong> of <strong>{{ $leaveRequests->total() }}</strong> requests
                    @else
                        Showing 0 requests
                    @endif
                </div>

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

            @if($leaveRequests->hasPages())
                <div>
                    {{ $leaveRequests->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
