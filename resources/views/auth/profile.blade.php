@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'User Profile')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="avatar-circle mx-auto mb-3 text-white fs-3" style="width: 80px; height: 80px; background: var(--primary-gradient) !important;">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
            <p class="text-muted text-sm mb-2">{{ $user->email }}</p>
            <div class="mb-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold">{{ $user->role?->name ?? 'User' }}</span>
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold">Active</span>
            </div>
            <hr>
            <div class="text-start text-sm">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Phone:</span>
                    <span class="fw-semibold">{{ $user->phone ?? 'Not set' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Joined:</span>
                    <span class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Edit Profile -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-person-gear me-2 text-primary"></i> Personal Information</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-shield-lock me-2 text-primary"></i> Change Password</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-sm">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-sm">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-key me-1"></i> Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
