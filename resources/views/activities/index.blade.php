@extends('layouts.app')

@section('title', 'Activities')
@section('page_title', 'Activity Tracker')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-check2-square text-primary me-2"></i> All Scheduled Activities</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('activities.index', ['status' => 'Pending']) }}" class="btn btn-sm {{ request('status') == 'Pending' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">Pending</a>
            <a href="{{ route('activities.index', ['status' => 'Completed']) }}" class="btn btn-sm {{ request('status') == 'Completed' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">Completed</a>
            <a href="{{ route('activities.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">All</a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Type</th>
                        <th>Activity Title</th>
                        <th>Lead</th>
                        <th>Due Date</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $act)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border">{{ $act->type }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $act->title }}</div>
                                @if($act->description)
                                    <div class="text-xs text-muted">{{ $act->description }}</div>
                                @endif
                            </td>
                            <td>
                                @if($act->lead)
                                    <a href="{{ route('leads.show', $act->lead_id) }}" class="text-decoration-none fw-semibold text-primary">
                                        {{ $act->lead->name }}
                                    </a>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
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
                                <span class="badge {{ $act->status == 'Completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} badge-status">
                                    {{ $act->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($act->status == 'Pending')
                                    <form action="{{ route('activities.complete', $act->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="bi bi-check-lg me-1"></i> Done
                                        </button>
                                    </form>
                                @endif
                                @if($act->lead)
                                    <a href="{{ route('leads.show', $act->lead_id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5 ms-1">
                                        View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-square fs-1 d-block mb-2 text-muted"></i>
                                No activities found.
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
                    @if($activities->total() > 0)
                        Showing <strong>{{ $activities->firstItem() }}</strong> to <strong>{{ $activities->lastItem() }}</strong> of <strong>{{ $activities->total() }}</strong> activities
                    @else
                        Showing 0 activities
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

            @if($activities->hasPages())
                <div>
                    {{ $activities->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
