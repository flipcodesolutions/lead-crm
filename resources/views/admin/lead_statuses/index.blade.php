@extends('layouts.app')

@section('title', 'Lead Statuses')
@section('page_title', 'Lead Statuses Master')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-tag-fill text-primary me-2"></i> Lead Statuses</h5>
        <a href="{{ route('admin.lead-statuses.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Status
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Status Name</th>
                        <th class="text-center">Total Leads</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $status)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $status->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $status->leads_count }} Leads</span>
                            </td>
                            <td>
                                <span class="badge {{ $status->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $status->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.lead-statuses.edit', $status->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                                    Edit
                                </a>
                                @if($status->leads_count == 0)
                                    <form action="{{ route('admin.lead-statuses.destroy', $status->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this status?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No statuses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($statuses->total() > 0)
                        Showing <strong>{{ $statuses->firstItem() }}</strong> to <strong>{{ $statuses->lastItem() }}</strong> of <strong>{{ $statuses->total() }}</strong> statuses
                    @else
                        Showing 0 statuses
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

            @if($statuses->hasPages())
                <div>
                    {{ $statuses->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
