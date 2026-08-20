@extends('layouts.app')

@section('title', 'Lead Sources')
@section('page_title', 'Lead Sources Master')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-signpost-2-fill text-primary me-2"></i> Lead Sources</h5>
        <a href="{{ route('admin.lead-sources.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Lead Source
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Source Name</th>
                        <th class="text-center">Total Leads</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sources as $source)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $source->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $source->leads_count }} Leads</span>
                            </td>
                            <td>
                                <span class="badge {{ $source->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $source->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.lead-sources.edit', $source->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                                    Edit
                                </a>
                                @if($source->leads_count == 0)
                                    <form action="{{ route('admin.lead-sources.destroy', $source->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this source?');">
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
                            <td colspan="4" class="text-center py-4 text-muted">No sources found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($sources->total() > 0)
                        Showing <strong>{{ $sources->firstItem() }}</strong> to <strong>{{ $sources->lastItem() }}</strong> of <strong>{{ $sources->total() }}</strong> sources
                    @else
                        Showing 0 sources
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

            @if($sources->hasPages())
                <div>
                    {{ $sources->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
