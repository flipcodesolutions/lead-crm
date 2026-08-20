@extends('layouts.app')

@section('title', 'Leave Types')
@section('page_title', 'Leave Type Master')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.leave-types.index') }}" method="GET" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search leave type name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                @if(request('search'))
                    <a href="{{ route('hr.leave-types.index') }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-calendar-heart text-primary me-1"></i> Leave Types</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $leaveTypes->total() }} Categories</span>
        </div>
        <a href="{{ route('hr.leave-types.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Leave Type
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Leave Type</th>
                        <th>Standard Quota</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveTypes as $lt)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $lt->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $lt->total_days }} days / year</span>
                            </td>
                            <td>
                                <span class="badge {{ $lt->is_paid ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $lt->is_paid ? 'Paid Leave' : 'Unpaid' }}
                                </span>
                            </td>
                            <td class="text-xs text-muted">
                                {{ $lt->description ?: '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $lt->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $lt->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('hr.leave-types.edit', $lt->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('hr.leave-types.destroy', $lt->id) }}" method="POST" onsubmit="return confirm('Delete this leave type?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No leave types defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($leaveTypes->total() > 0)
                        Showing <strong>{{ $leaveTypes->firstItem() }}</strong> to <strong>{{ $leaveTypes->lastItem() }}</strong> of <strong>{{ $leaveTypes->total() }}</strong> leave types
                    @else
                        Showing 0 leave types
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

            @if($leaveTypes->hasPages())
                <div>
                    {{ $leaveTypes->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
