@extends('layouts.app')

@section('title', 'Designations')
@section('page_title', 'Designation Management')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('hr.designations.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search designation by name or description..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('hr.designations.index') }}" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold m-0"><i class="bi bi-person-badge text-primary me-1"></i> Job Designations</h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $designations->total() }} Total</span>
        </div>
        <a href="{{ route('hr.designations.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Designation
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Designation Name</th>
                        <th>Description</th>
                        <th>Active Employees</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $desig)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $desig->name }}</div>
                            </td>
                            <td class="text-xs text-muted">
                                {{ $desig->description ?: '—' }}
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $desig->employees_count }} employee(s)</span>
                            </td>
                            <td>
                                <span class="badge {{ $desig->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $desig->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('hr.designations.edit', $desig->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('hr.designations.destroy', $desig->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this designation?');">
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
                            <td colspan="5" class="text-center py-4 text-muted">No designations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($designations->total() > 0)
                        Showing <strong>{{ $designations->firstItem() }}</strong> to <strong>{{ $designations->lastItem() }}</strong> of <strong>{{ $designations->total() }}</strong> designations
                    @else
                        Showing 0 designations
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

            @if($designations->hasPages())
                <div>
                    {{ $designations->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
