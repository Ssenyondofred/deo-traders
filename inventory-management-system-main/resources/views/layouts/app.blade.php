<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Accounting System') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
    
    <style>
        /* CSS Utama Layout */
        html, body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }

        .app-wrapper {
            display: flex !important;
            flex-direction: row !important;
            height: 100vh;
            width: 100%;
        }

        /* PERUBAHAN: Sidebar Menjadi Hitam Gelap Berkelas */
        .sidebar {
            width: 240px !important;
            min-width: 240px !important;
            max-width: 240px !important;
            background-color: #111f2c !important; /* Warna dasar gelap pekat */
            border-right: 1px solid #1a2e40 !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100vh !important;
        }

        .sidebar-nav-container {
            flex: 1 1 0%;
            overflow-y: auto;
            min-height: 0;
        }

        main {
            flex-grow: 1 !important;
            min-width: 0;
            background-color: #f8f9fa;
            height: 100vh;
            overflow-y: auto;
        }

        /* Custom Scrollbar untuk Area Gelap */
        .sidebar-nav-container::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-nav-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: #2b3e50;
            border-radius: 4px;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: #475e75;
        }

        /* PERUBAHAN: Membuat teks menu jauh lebih terang & kontras */
        .nav-link {
            color: #a0aec0 !important; /* Diubah ke abu-abu terang yang kontras di latar gelap */
            padding: 0.6rem 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 6px;
            margin-bottom: 2px;
            transition: all 0.2s ease;
        }

        /* Hover Effect: Saat diarahkan mouse, tulisan langsung berubah jadi putih bersih */
        .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Status Menu Aktif / Terpilih tetap putih solid terang */
        .nav-link.active {
            background-color: #1a2e40 !important; 
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: inset 4px 0 0 #0d6efd; 
        }

        /* Menu yang di-disable dibuat sedikit lebih terang dari sebelumnya */
        .nav-link.disabled {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        /* PERUBAHAN: Teks Kategori (INVENTORY, TRANSAKSI, dll) dibuat lebih putih tegas */
        .nav-section {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #cbd5e1 !important; /* Putih keabu-abuan terang agar langsung terbaca */
            font-weight: 700;
            padding: 1.2rem 1rem 0.4rem;
        }

        /* PERUBAHAN: Area Footer Tombol Logout */
        .sidebar-footer {
            background-color: #111f2c;
            z-index: 10;
        }
    </style>
</head>
<body>
<div class="d-flex app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand px-3 py-3" style="border-bottom: 1px solid #1a2e40;">
            <div class="fw-bold text-white fs-5"></div>
            <small style="color: #ffffff;">Accounting System</small>
        </div>
        
        <div class="px-3 py-2 small text-white" style="border-bottom: 1px solid #1a2e40; background-color: rgba(0,0,0,0.15);">
            <i class="bi bi-person-circle me-1 text-info"></i> {{ auth()->user()->name }}<br>
            <span class="badge mt-1" style="background-color: #243b53; color: #ffffff;">{{ auth()->user()->role->name }}</span>
        </div>

        <div class="sidebar-nav-container">
            <nav class="nav flex-column px-2 py-2">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                @if(auth()->user()->hasPermission('inventory.view'))
                    <div class="nav-section">Inventory</div>
                    <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">Stock of Goods</a>
                    @if(auth()->user()->hasPermission('stock_report.view'))
                        <a class="nav-link {{ request()->routeIs('stock-report.*') ? 'active' : '' }}" href="{{ route('stock-report.index') }}">Stock Report</a>
                    @endif
                @endif

                @if(auth()->user()->hasPermission('sales.view') || auth()->user()->hasPermission('purchases.view'))
                    <div class="nav-section">Transaction</div>
                @endif
                @if(auth()->user()->hasPermission('sales.view'))
                    <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Sales</a>
                @endif
                @if(auth()->user()->hasPermission('purchases.view'))
                    <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">Purchases</a>
                @endif
                @if(auth()->user()->hasPermission('expenses.view'))
                    <a class="nav-link disabled" href="#">Expenses</a>
                @endif
                @if(auth()->user()->hasPermission('production.view'))
                    <a class="nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}" href="{{ route('production.index') }}">Production</a>
                @endif

                @if(auth()->user()->isAdmin())
                    <div class="nav-section">Admin Control</div>
                    <a class="nav-link disabled" href="#">Transaction Review</a>

                    <a class="nav-link {{ request()->is('ar*') ? 'active' : '' }}" href="{{ route('ar.index') }}">
                        <i class="bi bi-cash-coin"></i> Accounts Receivable (AR)
                    </a>
                    <a class="nav-link {{ request()->is('ap*') ? 'active' : '' }}" href="{{ route('ap.index') }}">
                        <i class="bi bi-credit-card"></i> Accounts Payable (AP)
                    </a>
                    <a class="nav-link {{ request()->is('journals*') ? 'active' : '' }}" href="{{ route('journals.index') }}">
                        <i class="bi bi-journal-text"></i> General Journal
                    </a>
                    <a class="nav-link {{ request()->is('ledger*') ? 'active' : '' }}" href="{{ route('ledger.index') }}">
                        <i class="bi bi-book"></i> General Ledger
                    </a>
                    <a class="nav-link {{ request()->is('financial*') ? 'active' : '' }}" href="{{ route('financial.index') }}">
                        <i class="bi bi-bar-chart-line"></i> Financial Reports
                    </a>

                    <div class="nav-section">Admin</div>
                    <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->is('activity-logs*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Activity Log
                    </a>
                @endif
            </nav>
        </div>

        <div class="sidebar-footer px-3 py-3" style="border-top: 1px solid #1a2e40; mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-left"></i> Log Out
                </button>
            </form>
        </div>
    </aside>

    <main>
        <header class="topbar border-bottom px-4 py-3 d-flex justify-content-between align-items-center bg-white sticky-top">
            <h1 class="h5 mb-0">@yield('page_title', 'Dashboard')</h1>
            <span class="text-muted small">{{ now()->format('d M Y') }}</span>
        </header>
        <div class="content-area p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
</body>
</html>