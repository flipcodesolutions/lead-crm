<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - {{ \App\Models\Setting::get('company_name', config('app.name', 'Odoo CRM')) }}</title>

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
            background: var(--auth-bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            padding: 1.5rem;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-header {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .auth-body {
            padding: 2rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.65rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.45);
        }

        .demo-badge {
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.5rem;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            color: #475569;
        }

        .demo-badge:hover {
            border-color: #4f46e5;
            background: #eef2ff;
            color: #4f46e5;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="auth-card">
        @yield('content')
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
