@extends('layouts.app')

@section('title', 'Tax Slabs Master')
@section('page_title', 'Income Tax Slabs & Regime')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 bg-light">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-calculator-fill text-primary me-2"></i> Dynamic Tax Slabs Configuration</h5>
                <p class="text-xs text-muted mb-0">Manage progressive tax slabs for automated monthly payroll TDS and annual tax computations.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('hr.tax-calculator') }}" class="btn btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-calculator me-1"></i> Tax Simulation Tool
                </a>
                <a href="{{ route('hr.tax-slabs.create') }}" class="btn btn-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Tax Slab
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0"><i class="bi bi-layers text-primary me-2"></i> Configured Tax Brackets</h6>
        <span class="badge bg-secondary-subtle text-secondary">{{ count($slabs) }} Slabs</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Slab Name</th>
                        <th>Minimum Income (₹)</th>
                        <th>Maximum Income (₹)</th>
                        <th>Tax Rate (%)</th>
                        <th>Fixed Base Tax (₹)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slabs as $slab)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $slab->name }}</div>
                            </td>
                            <td class="fw-semibold">@inr($slab->min_income)</td>
                            <td>
                                @if($slab->max_income)
                                    <span class="fw-semibold">@inr($slab->max_income)</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">No Upper Limit (Above @inr($slab->min_income))</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger fs-6">{{ $slab->tax_rate }}%</span>
                            </td>
                            <td>@inr($slab->fixed_tax)</td>
                            <td>
                                <span class="badge {{ $slab->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $slab->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('hr.tax-slabs.edit', $slab->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('hr.tax-slabs.destroy', $slab->id) }}" method="POST" onsubmit="return confirm('Delete this tax slab?');">
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
                            <td colspan="7" class="text-center py-4 text-muted">No tax slabs configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
