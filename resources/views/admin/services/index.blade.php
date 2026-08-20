@extends('layouts.app')

@section('title', 'Services & Products')
@section('page_title', 'Products & Services Catalog')

@php
    $currency = \App\Models\Setting::get('currency_symbol', '$');
@endphp

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-box-seam-fill text-primary me-2"></i> Catalog Items</h5>
        <a href="{{ route('admin.services.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Service / Product
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Product / Service</th>
                        <th>Description</th>
                        <th class="text-end">Base Price</th>
                        <th class="text-center">Tax (%)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $svc)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $svc->name }}</td>
                            <td class="text-muted text-sm text-truncate" style="max-width: 300px;">{{ $svc->description ?? '—' }}</td>
                            <td class="text-end fw-bold text-dark">{{ $currency }}{{ number_format($svc->price, 2) }}</td>
                            <td class="text-center">{{ $svc->tax_percentage }}%</td>
                            <td>
                                <span class="badge {{ $svc->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $svc->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.services.edit', $svc->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                                    Edit
                                </a>
                                <form action="{{ route('admin.services.destroy', $svc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product/service?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No products or services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($services->total() > 0)
                        Showing <strong>{{ $services->firstItem() }}</strong> to <strong>{{ $services->lastItem() }}</strong> of <strong>{{ $services->total() }}</strong> services
                    @else
                        Showing 0 services
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

            @if($services->hasPages())
                <div>
                    {{ $services->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
