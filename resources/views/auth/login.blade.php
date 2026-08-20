@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="auth-header">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-3 mb-3 shadow" style="background: var(--primary-gradient) !important;">
        <i class="bi bi-diagram-3-fill fs-2"></i>
    </div>
    <h3 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">{{ \App\Models\Setting::get('company_name', 'Odoo Lead CRM') }}</h3>
    <p class="text-muted text-sm mb-0">Sign in to your CRM workspace</p>
</div>

<div class="auth-body">
    @if(session('info'))
        <div class="alert alert-info py-2 text-sm rounded-3 mb-3">{{ session('info') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 text-sm rounded-3 mb-3">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('login.submit') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold text-sm">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" id="email" class="form-control border-start-0 ps-0" placeholder="name@company.com" value="{{ old('email') }}" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label fw-semibold text-sm">Password</label>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
            </div>
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label text-sm text-muted" for="remember">Keep me signed in</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-4">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to CRM
        </button>
    </form>

    <!-- Quick Demo Accounts -->
    <div class="border-top pt-3">
        <span class="d-block text-center text-xs text-muted mb-2 fw-semibold text-uppercase tracking-wider">Quick Demo Login</span>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <span class="demo-badge" onclick="fillLogin('admin@crm.com', 'password123')">
                <i class="bi bi-person-fill-gear me-1 text-danger"></i> Admin
            </span>
            <span class="demo-badge" onclick="fillLogin('hr@crm.com', 'password123')">
                <i class="bi bi-person-badge-fill me-1 text-warning"></i> HR
            </span>
            <span class="demo-badge" onclick="fillLogin('manager@crm.com', 'password123')">
                <i class="bi bi-person-workspace me-1 text-primary"></i> Manager
            </span>
            <span class="demo-badge" onclick="fillLogin('telecaller@crm.com', 'password123')">
                <i class="bi bi-telephone-fill me-1 text-info"></i> Telecaller
            </span>
            <span class="demo-badge" onclick="fillLogin('sales@crm.com', 'password123')">
                <i class="bi bi-briefcase-fill me-1 text-success"></i> Salesperson
            </span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endpush
