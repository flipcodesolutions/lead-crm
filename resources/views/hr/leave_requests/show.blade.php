@extends('layouts.app')

@section('title', 'Review Leave Request')
@section('page_title', 'Leave Application Review')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0"><i class="bi bi-calendar2-check text-primary me-2"></i> Leave Request Details</h5>
                @php
                    $badgeClass = match($leaveRequest->status) {
                        'Approved' => 'bg-success-subtle text-success',
                        'Pending' => 'bg-warning-subtle text-warning',
                        'Rejected' => 'bg-danger-subtle text-danger',
                        'Cancelled' => 'bg-secondary-subtle text-secondary',
                        default => 'bg-light text-dark',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} badge-status fs-6">{{ $leaveRequest->status }}</span>
            </div>
            <div class="card-body p-4">
                <div class="p-3 bg-light rounded-3 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-xs text-muted text-uppercase d-block">Employee</span>
                            <a href="{{ route('hr.employees.show', $leaveRequest->employee_id) }}" class="fw-bold text-dark text-decoration-none fs-5">
                                {{ $leaveRequest->employee->full_name }}
                            </a>
                            <div class="text-xs text-muted">{{ $leaveRequest->employee->employee_code }} &bull; {{ $leaveRequest->employee->department->name ?? 'Staff' }}</div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-xs text-muted text-uppercase d-block">Reporting Manager</span>
                            <div class="fw-semibold text-dark">{{ $leaveRequest->employee->manager->full_name ?? 'Direct Management' }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4 text-sm">
                    <div class="col-md-4">
                        <span class="text-muted d-block text-xs text-uppercase">Leave Category</span>
                        <div class="fw-bold text-primary fs-6">{{ $leaveRequest->leaveType->name }}</div>
                        <span class="text-xs text-muted">{{ $leaveRequest->leaveType->is_paid ? 'Paid Absence' : 'Unpaid' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block text-xs text-uppercase">Date Range</span>
                        <div class="fw-bold text-dark">{{ $leaveRequest->from_date->format('d M Y') }} &mdash; {{ $leaveRequest->to_date->format('d M Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block text-xs text-uppercase">Total Duration</span>
                        <div class="fs-5 fw-bold text-dark">{{ $leaveRequest->total_days }} day(s)</div>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-muted d-block text-xs text-uppercase mb-1">Reason for Leave</span>
                    <div class="p-3 bg-light border rounded-3 text-dark">
                        {{ $leaveRequest->reason }}
                    </div>
                </div>

                @if($leaveRequest->status === 'Rejected' && $leaveRequest->rejected_reason)
                    <div class="alert alert-danger mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-1"></i> Rejection Reason:</h6>
                        <p class="mb-0 text-sm">{{ $leaveRequest->rejected_reason }}</p>
                    </div>
                @endif

                @if($leaveRequest->approver)
                    <div class="p-3 bg-light rounded-3 mb-4 text-xs">
                        <span class="text-muted">Reviewed and processed by:</span>
                        <strong>{{ $leaveRequest->approver->name }}</strong> on <strong>{{ $leaveRequest->approved_at?->format('d M Y, h:i A') }}</strong>
                    </div>
                @endif

                <!-- Workflow Actions -->
                <div class="d-flex justify-content-between pt-3 border-top">
                    <a href="{{ route('hr.leave-requests.index') }}" class="btn btn-light rounded-pill px-4">
                        &larr; Back to List
                    </a>

                    <div class="d-flex gap-2">
                        @if(auth()->user()->canManageHR() && $leaveRequest->status === 'Pending')
                            <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                Reject Request
                            </button>
                            <form action="{{ route('hr.leave-requests.approve', $leaveRequest->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4" onclick="return confirm('Approve this leave application?');">
                                    Approve Request
                                </button>
                            </form>
                        @endif

                        @if($leaveRequest->status !== 'Cancelled')
                            <form action="{{ route('hr.leave-requests.cancel', $leaveRequest->id) }}" method="POST" onsubmit="return confirm('Cancel this leave request?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary rounded-pill px-3">
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('hr.leave-requests.reject', $leaveRequest->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Reject Leave Application</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold text-sm">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea name="rejected_reason" class="form-control" rows="3" placeholder="Provide note explaining the reason..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
