<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lead CRM') - {{ \App\Models\Setting::get('company_name', config('app.name', 'Odoo CRM')) }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Master CRM Theme CSS (Change color variables in public/css/crm-theme.css) -->
    <link rel="stylesheet" href="{{ asset('css/crm-theme.css') }}">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--content-bg);
            color: var(--text-main);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1040;
        }

        #sidebar .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            border-bottom: 1px solid #1e293b;
        }

        #sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.7rem 1.25rem;
            margin: 0.2rem 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-weight: 500;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        #sidebar .nav-link:hover {
            color: #fff;
            background: var(--sidebar-hover);
        }

        #sidebar .nav-link.active {
            color: var(--sidebar-text-active);
            background: var(--primary-gradient);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        #sidebar .nav-section-title {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 1.2rem 1.5rem 0.4rem;
        }

        /* Main Content Layout */
        #main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .topbar {
            height: 70px;
            background: var(--topbar-bg);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .main-content {
            padding: 1.75rem;
            flex: 1;
        }

        /* Card enhancements */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            box-shadow: var(--card-shadow);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        /* Buttons & Badges */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35);
        }

        .badge-status {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35em 0.75em;
            border-radius: 9999px;
        }

        /* Odoo Stage Progress Bar */
        .stage-pipeline {
            display: flex;
            overflow-x: auto;
            background: #f1f5f9;
            border-radius: 0.6rem;
            padding: 0.25rem;
            gap: 0.25rem;
        }

        .stage-pipeline-step {
            flex: 1;
            min-width: 110px;
            text-align: center;
            padding: 0.5rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
            border-radius: 0.45rem;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }

        .stage-pipeline-step.active {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }

        .stage-pipeline-step.completed {
            background: #e0e7ff;
            color: #3730a3;
        }

        /* Odoo Kanban Board */
        .kanban-board {
            display: flex;
            gap: 1.25rem;
            overflow-x: auto;
            padding-bottom: 1.5rem;
            min-height: calc(100vh - 220px);
        }

        .kanban-column {
            flex: 0 0 310px;
            background: #f1f5f9;
            border-radius: 0.85rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 200px);
        }

        .kanban-column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.85rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .kanban-cards-container {
            flex: 1;
            overflow-y: auto;
            padding-right: 4px;
            min-height: 120px;
        }

        .kanban-card {
            background: #ffffff;
            border-radius: 0.65rem;
            padding: 1rem;
            margin-bottom: 0.85rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            cursor: grab;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        }

        /* Avatar Icon */
        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #475569;
            font-size: 0.85rem;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex">

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="d-flex flex-column flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2" style="width: 38px; height: 38px; background: var(--primary-gradient) !important;">
                <i class="bi bi-diagram-3-fill fs-5"></i>
            </div>
            <div>
                <span class="d-block lh-1 brand-font">{{ \App\Models\Setting::get('company_name', 'Odoo CRM') }}</span>
                <span class="text-xs text-muted fw-normal" style="font-size: 0.7rem; color: #64748b !important;">Lead & Pipeline Hub</span>
            </div>
        </a>

        <div class="flex-grow-1 overflow-y-auto py-2">
            <!-- Core Navigation -->
            @if(auth()->user()->isHR())
                <div class="nav-section-title">Overview</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('hr.dashboard') }}" class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>HR Dashboard</span>
                        </a>
                    </li>
                </ul>
            @else
                <div class="nav-section-title">Overview</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                <!-- CRM Modules -->
                <div class="nav-section-title">Lead Management</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('leads.index') }}" class="nav-link {{ request()->routeIs('leads.index') || request()->routeIs('leads.show') || request()->routeIs('leads.create') || request()->routeIs('leads.edit') ? 'active' : '' }}">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Leads</span>
                        </a>
                    </li>
                    @if(in_array(auth()->user()->role?->name, ['Admin', 'Manager']))
                    <li class="nav-item">
                        <a href="{{ route('leads.import') }}" class="nav-link {{ request()->routeIs('leads.import*') ? 'active' : '' }}">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            <span>Import Leads</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('opportunities.index') }}" class="nav-link {{ request()->routeIs('opportunities.*') ? 'active' : '' }}">
                            <i class="bi bi-kanban-fill"></i>
                            <span>Sales Pipeline</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('follow-ups.index') }}" class="nav-link {{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Follow-ups</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activities.index') }}" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            <span>Activities</span>
                        </a>
                    </li>
                </ul>

                <!-- Sales Modules -->
                <div class="nav-section-title">Sales & Billing</div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('quotations.index') }}" class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i>
                            <span>Quotations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line-fill"></i>
                            <span>Analytics & Reports</span>
                        </a>
                    </li>
                </ul>
            @endif

            <!-- HR & Payroll Module (Visible to Admin, Manager, and HR) -->
            @if(auth()->user()->canManageHR())
            <div class="nav-section-title">HR & Payroll</div>
            <ul class="nav nav-pills flex-column mb-auto">
                @if(!auth()->user()->isHR())
                <li class="nav-item">
                    <a href="{{ route('hr.dashboard') }}" class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>HR Dashboard</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('hr.employees.index') }}" class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Employees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.departments.index') }}" class="nav-link {{ request()->routeIs('hr.departments.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Departments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.designations.index') }}" class="nav-link {{ request()->routeIs('hr.designations.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Designations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.salaries.index') }}" class="nav-link {{ request()->routeIs('hr.salaries.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Salary & CTC</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.leave-requests.index') }}" class="nav-link {{ request()->routeIs('hr.leave-requests.*') || request()->routeIs('hr.leave-allocations.*') || request()->routeIs('hr.leave-types.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-check-fill"></i>
                        <span>Leaves & Offs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.payrolls.index') }}" class="nav-link {{ request()->routeIs('hr.payrolls.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>Monthly Payroll</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.tax-slabs.index') }}" class="nav-link {{ request()->routeIs('hr.tax-slabs.*') || request()->routeIs('hr.tax-calculator') ? 'active' : '' }}">
                        <i class="bi bi-percent"></i>
                        <span>Tax Slabs & TDS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hr.leave-reports.index') }}" class="nav-link {{ request()->routeIs('hr.leave-reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>HR Reports</span>
                    </a>
                </li>
            </ul>
            @endif

            <!-- Administration / Masters -->
            @if(auth()->user()->isAdmin())
            <div class="nav-section-title">System & Masters</div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Users & Team</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>Roles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.lead-sources.index') }}" class="nav-link {{ request()->routeIs('admin.lead-sources.*') ? 'active' : '' }}">
                        <i class="bi bi-signpost-2-fill"></i>
                        <span>Lead Sources</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.lead-statuses.index') }}" class="nav-link {{ request()->routeIs('admin.lead-statuses.*') ? 'active' : '' }}">
                        <i class="bi bi-tag-fill"></i>
                        <span>Lead Statuses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.lead-stages.index') }}" class="nav-link {{ request()->routeIs('admin.lead-stages.*') ? 'active' : '' }}">
                        <i class="bi bi-layers-fill"></i>
                        <span>Pipeline Stages</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i>
                        <span>Products & Services</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear-fill"></i>
                        <span>CRM Settings</span>
                    </a>
                </li>
            </ul>
            @endif
        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3 border-top border-secondary-subtle" style="border-color: #1e293b !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle bg-primary text-white" style="background: var(--primary-gradient) !important;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white fw-bold text-truncate text-sm">{{ auth()->user()->name }}</div>
                    <span class="badge bg-secondary text-xs" style="font-size: 0.65rem;">{{ auth()->user()->role?->name ?? 'User' }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link text-secondary p-0 text-decoration-none" title="Logout">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Application Wrapper -->
    <div id="main-wrapper" class="flex-grow-1">
        <!-- Top Navbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <h4 class="m-0 fw-bold brand-font text-dark">@yield('page_title', 'CRM Hub')</h4>
            </div>

            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Quick Create Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle rounded-pill px-3 py-1 d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg"></i> Quick Action
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li><a class="dropdown-item py-2" href="{{ route('leads.create') }}"><i class="bi bi-funnel me-2 text-primary"></i> Create Lead</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('opportunities.create') }}"><i class="bi bi-kanban me-2 text-info"></i> New Opportunity</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('quotations.create') }}"><i class="bi bi-receipt me-2 text-success"></i> New Quotation</a></li>
                    </ul>
                </div>

                <!-- Notifications Bell -->
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                    $recentNotifs = auth()->user()->customNotifications()->latest()->take(5)->get();
                @endphp
                <div class="dropdown">
                    <button class="btn btn-light position-relative rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell-fill fs-5 text-secondary"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2" style="width: 320px;">
                        <div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom">
                            <span class="fw-bold text-sm">Notifications</span>
                            @if($unreadCount > 0)
                                <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-xs p-0 text-decoration-none">Mark all read</button>
                                </form>
                            @endif
                        </div>
                        <div class="py-1" style="max-height: 250px; overflow-y: auto;">
                            @forelse($recentNotifs as $notif)
                                <a href="{{ route('notifications.read', $notif->id) }}" class="dropdown-item py-2 rounded-2 {{ is_null($notif->read_at) ? 'bg-light fw-bold' : '' }}">
                                    <div class="text-xs text-primary">{{ $notif->title }}</div>
                                    <div class="text-muted text-xs text-truncate">{{ $notif->message }}</div>
                                    <div class="text-muted" style="font-size: 0.65rem;">{{ $notif->created_at->diffForHumans() }}</div>
                                </a>
                            @empty
                                <div class="text-center text-muted py-3 text-xs">No notifications yet.</div>
                            @endforelse
                        </div>
                        <div class="border-top pt-1 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-primary text-decoration-none">View All Notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light rounded-pill px-3 py-1 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <div class="avatar-circle bg-primary text-white" style="width: 28px; height: 28px; font-size: 0.75rem;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <span class="fw-semibold text-sm d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down text-muted text-xs"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ auth()->user()->name }}</div>
                            <div class="text-muted text-xs">{{ auth()->user()->email }}</div>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                        @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item py-2" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Global Flash Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Please check following errors:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
