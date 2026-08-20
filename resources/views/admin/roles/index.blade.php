@extends('layouts.app')

@section('title', 'Role Management')
@section('page_title', 'User Roles')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Roles</h5>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Role
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-xs text-uppercase">
                    <tr>
                        <th class="ps-4">Role Name</th>
                        <th>Description</th>
                        <th class="text-center">Assigned Users</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $role->name }}</td>
                            <td class="text-muted text-sm">{{ $role->description ?? 'No description provided.' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary">{{ $role->users_count }} Users</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                                    Edit
                                </a>
                                @if($role->users_count == 0)
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
