@extends('layouts.app')

@section('title', 'Pipeline Stages')
@section('page_title', 'Pipeline Stages Master')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold m-0"><i class="bi bi-layers-fill text-primary me-2"></i> Pipeline Stages</h5>
            <span class="text-xs text-muted">Controls stages on the Odoo Kanban Board & Lead detail tracker</span>
        </div>
        <a href="{{ route('admin.lead-stages.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Stage
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Sort Order</th>
                        <th>Stage Name</th>
                        <th class="text-center">Leads</th>
                        <th class="text-center">Opportunities</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stages as $stage)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#{{ $stage->sort_order }}</td>
                            <td class="fw-bold text-dark">{{ $stage->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $stage->leads_count }} Leads</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info">{{ $stage->opportunities_count }} Deals</span>
                            </td>
                            <td>
                                <span class="badge {{ $stage->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $stage->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.lead-stages.edit', $stage->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                                    Edit
                                </a>
                                @if($stage->leads_count == 0 && $stage->opportunities_count == 0)
                                    <form action="{{ route('admin.lead-stages.destroy', $stage->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this pipeline stage?');">
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
                            <td colspan="6" class="text-center py-4 text-muted">No stages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
