@extends('layouts.app')

@section('title', 'User Management')
@section('page_title', 'User Management')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-people-fill text-primary me-2"></i> System Users</h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-person-plus me-1"></i> Add New User
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Email & Phone</th>
                        <th>Role</th>
                        <th class="text-center">Assigned Leads</th>
                        <th class="text-center">Deals</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-primary text-white" style="background: var(--primary-gradient) !important;">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="fw-bold text-dark">{{ $u->name }}</div>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-envelope text-muted me-1"></i> {{ $u->email }}</div>
                                @if($u->phone)
                                    <div class="text-xs text-muted"><i class="bi bi-telephone text-muted me-1"></i> {{ $u->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary badge-status">{{ $u->role?->name ?? 'None' }}</span>
                            </td>
                            <td class="text-center fw-semibold">{{ $u->leads_count }}</td>
                            <td class="text-center fw-semibold text-info">{{ $u->opportunities_count }}</td>
                            <td>
                                <span class="badge {{ $u->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-status">
                                    {{ $u->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
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
                            <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per-Page Footer -->
        <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-muted text-xs">
                    @if($users->total() > 0)
                        Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                    @else
                        Showing 0 users
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

            @if($users->hasPages())
                <div>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
