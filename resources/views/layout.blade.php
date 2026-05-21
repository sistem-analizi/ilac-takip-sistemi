<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlaç Takip Sistemi | Smart Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        const activeTheme = localStorage.getItem('appTheme') || 'default';
        if (activeTheme !== 'default') document.documentElement.setAttribute('data-theme', activeTheme);

        const activeMode = localStorage.getItem('bgMode') || 'light';
        if (activeMode === 'dark') document.documentElement.setAttribute('data-bg-mode', 'dark');

        if (localStorage.getItem('sidebarState') === 'collapsed' && window.innerWidth > 991) {
            document.documentElement.setAttribute('data-sidebar', 'collapsed');
        }
    </script>

    <style>
        /* =========================================
           KARANLIK MOD (DARK MODE) OVERRIDES
           (Ana renk değişkenleri app.css içine taşındı)
           ========================================= */
        [data-bg-mode="dark"] body { background-color: var(--bg-canvas) !important; color: var(--text-main) !important; }

        [data-bg-mode="dark"] .text-dark,
        [data-bg-mode="dark"] h1,
        [data-bg-mode="dark"] h2,
        [data-bg-mode="dark"] h3,
        [data-bg-mode="dark"] h4,
        [data-bg-mode="dark"] h5,
        [data-bg-mode="dark"] h6,
        [data-bg-mode="dark"] p,
        [data-bg-mode="dark"] span { color: var(--text-main) !important; }

        [data-bg-mode="dark"] .text-muted { color: var(--text-muted) !important; }
        [data-bg-mode="dark"] .text-danger { color: #fb7185 !important; }
        [data-bg-mode="dark"] .text-success { color: #34d399 !important; }

        [data-bg-mode="dark"] input,
        [data-bg-mode="dark"] select,
        [data-bg-mode="dark"] textarea { color: var(--text-main) !important; }
        [data-bg-mode="dark"] ::placeholder { color: rgba(255, 255, 255, 0.6) !important; opacity: 1; }

        [data-bg-mode="dark"] .bg-white,
        [data-bg-mode="dark"] .premium-card,
        [data-bg-mode="dark"] .compartment-box,
        [data-bg-mode="dark"] .timeline-item,
        [data-bg-mode="dark"] .id-card-item { background-color: var(--bg-surface) !important; border-color: var(--border-color) !important; }

        [data-bg-mode="dark"] .bg-light { background-color: rgba(255,255,255,0.03) !important; }
        [data-bg-mode="dark"] .border-light,
        [data-bg-mode="dark"] .border,
        [data-bg-mode="dark"] .border-bottom { border-color: var(--border-color) !important; }

        [data-bg-mode="dark"] .form-control,
        [data-bg-mode="dark"] .form-select,
        [data-bg-mode="dark"] .modern-input,
        [data-bg-mode="dark"] .search-input { background-color: var(--bg-canvas) !important; border-color: var(--border-color) !important; }

        [data-bg-mode="dark"] .modern-input:focus,
        [data-bg-mode="dark"] .search-input:focus { background-color: var(--bg-surface) !important; }
        [data-bg-mode="dark"] .modal-content { background-color: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; }
        [data-bg-mode="dark"] .list-group-item { background-color: transparent !important; border-color: var(--border-color) !important; }


        /* =========================================
           GENEL DÜZEN VE İSKELET
           ========================================= */
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 1.05rem;
            background-color: var(--bg-canvas);
            color: var(--text-main);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Sidebar Sınıfları */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            z-index: 1040;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        html[data-sidebar="collapsed"] .sidebar { width: var(--sidebar-mini-width); }

        .sidebar-header { padding: 2rem 1.5rem 1.5rem; display: flex; align-items: center; gap: 15px; white-space: nowrap; }
        html[data-sidebar="collapsed"] .sidebar-header { padding: 2rem 0 1.5rem 0; flex-direction: column; gap: 20px; }

        .sidebar-toggle-btn { background: transparent; border: none; color: var(--text-muted); font-size: 1.8rem; padding: 0; cursor: pointer; transition: all 0.2s ease; }
        .sidebar-toggle-btn:hover { color: var(--primary); }

        .logo-icon { width: 50px; height: 50px; background: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }

        .brand-text h5 { color: var(--text-main); font-size: 1.4rem; }
        .brand-text p { color: var(--text-muted); font-size: 0.9rem; }
        html[data-sidebar="collapsed"] .brand-text { display: none; }

        /* Menü Elemanları */
        .nav-menu { padding: 1rem; flex-grow: 1; }
        .nav-item-custom {
            display: flex; align-items: center; padding: 1.1rem 1.2rem;
            color: var(--text-muted); text-decoration: none; border-radius: 12px;
            margin-bottom: 0.5rem; font-weight: 500; font-size: 1.05rem;
            transition: all 0.2s ease; white-space: nowrap; overflow: hidden;
        }
        .nav-item-custom i { font-size: 1.4rem; margin-right: 15px; color: var(--text-muted); transition: all 0.2s ease; flex-shrink: 0; }
        .nav-item-custom:hover, .nav-item-custom.active { background: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary); }
        .nav-item-custom:hover i, .nav-item-custom.active i { color: var(--primary); }

        html[data-sidebar="collapsed"] .nav-menu { padding: 1rem 0.5rem; }
        html[data-sidebar="collapsed"] .nav-item-custom { justify-content: center; padding: 1rem; }
        html[data-sidebar="collapsed"] .nav-item-custom i { margin-right: 0; font-size: 1.6rem; }
        html[data-sidebar="collapsed"] .nav-item-text { display: none; }

        .sidebar-footer { padding: 2rem 1.5rem; border-top: 1px solid var(--border-color); }
        html[data-sidebar="collapsed"] .sidebar-footer { display: none; }
        .device-status-box { background: var(--bg-canvas); border: 1px solid var(--border-color); border-radius: 1rem; padding: 1rem; text-align: center; }

        /* Ana İçerik Alanı */
        .main-wrapper {
            margin-left: var(--sidebar-width); min-height: 100vh; display: flex;
            flex-direction: column;
            transition: margin-left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        html[data-sidebar="collapsed"] .main-wrapper { margin-left: var(--sidebar-mini-width); }

        .mobile-header { display: none; background: var(--bg-surface); padding: 1rem 1.5rem; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 1030; }
        .content-area { padding: 3rem 2rem; flex-grow: 1; }

        /* Floating Action Button (FAB) */
        .fab-btn { position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); z-index: 1045; transition: all 0.3s ease; text-decoration: none; }
        .fab-btn:hover { transform: translateY(-5px) scale(1.05); color: white; }

        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 1035; display: none; opacity: 0; transition: opacity 0.3s ease; }
        .sidebar-overlay.show { display: block; opacity: 1; }

        /* Mobil Optimizasyonları */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); border-radius: 0; width: var(--sidebar-width) !important; }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper, html[data-sidebar="collapsed"] .main-wrapper { margin-left: 0; }
            .mobile-header { display: flex; }
            .content-area { padding: 1.5rem 1rem; }
            #desktopSidebarToggle { display: none !important; }
            .sidebar-header { flex-direction: row !important; padding: 2rem 1.5rem 1.5rem !important; }
            .brand-text, .nav-item-text, .sidebar-footer { display: block !important; opacity: 1 !important; }
            .nav-item-custom i { margin-right: 15px !important; }
            .nav-item-custom { justify-content: flex-start !important; }
            .fab-btn { bottom: 20px; right: 20px; width: 55px; height: 55px; font-size: 1.6rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle-btn d-none d-lg-flex" id="desktopSidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-flex align-items-center gap-3">
            <div class="logo-icon animate__animated animate__pulse animate__infinite animate__slower">
                <i class="bi bi-capsule"></i>
            </div>
            <div class="brand-text">
                <h5 class="fw-bold mb-0" style="letter-spacing: 1px;">İlaç Takip</h5>
                <p class="small mb-0" style="font-size: 0.9rem;">Kolayca Takip</p>
            </div>
        </div>
    </div>

    <div class="nav-menu mt-2">
        <a href="{{ route('dashboard') }}" class="nav-item-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> <span class="nav-item-text">Kontrol Paneli</span>
        </a>
        <a href="{{ route('ilac.index') }}" class="nav-item-custom {{ request()->routeIs('ilac.index') ? 'active' : '' }}">
            <i class="bi bi-calendar2-week-fill"></i> <span class="nav-item-text">Programım</span>
        </a>
        <a href="{{ route('gecmis.index') }}" class="nav-item-custom {{ request()->routeIs('gecmis.index') ? 'active' : '' }}">
            <i class="bi bi-pie-chart-fill"></i> <span class="nav-item-text">İlaç Geçmişi</span>
        </a>
        <a href="{{ route('profil.index') }}" class="nav-item-custom {{ request()->routeIs('profil.index') ? 'active' : '' }}">
            <i class="bi bi-person-bounding-box"></i> <span class="nav-item-text">Profilim</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="device-status-box">
            <h6 class="fw-bold mb-1" style="font-size: 1.1rem;">Cihaz Aktif</h6>
            <p class="text-muted mb-3" style="font-size: 0.9rem;">Sistem donanımla senkronize.</p>
            <a href="{{ route('ayarlar.index') }}" class="btn w-100 rounded-pill fw-bold" style="background-color: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-color); padding: 0.6rem 1rem;">
                Ayarlar
            </a>
        </div>
    </div>
</aside>

<main class="main-wrapper" id="mainWrapper">
    <header class="mobile-header">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                <i class="bi bi-capsule fs-5"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0 m-0">SmartPill</h5>
        </div>
        <button class="btn border-0 p-1" id="mobileSidebarToggle" style="background: transparent;">
            <i class="bi bi-list fs-1 text-muted"></i>
        </button>
    </header>

    <div class="content-area">
        <div class="container-fluid max-w-custom">
            @yield('content')
        </div>
    </div>
</main>

<a href="{{ route('ilac.create') }}" class="fab-btn" title="Yeni İlaç Planla">
    <i class="bi bi-plus-lg"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const mobileToggleBtn = document.getElementById('mobileSidebarToggle');
        const desktopToggleBtn = document.getElementById('desktopSidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if(desktopToggleBtn) {
            desktopToggleBtn.addEventListener('click', () => {
                const isCollapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
                if(isCollapsed) {
                    document.documentElement.removeAttribute('data-sidebar');
                    localStorage.setItem('sidebarState', 'expanded');
                } else {
                    document.documentElement.setAttribute('data-sidebar', 'collapsed');
                    localStorage.setItem('sidebarState', 'collapsed');
                }
            });
        }

        function toggleMobileSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        if(mobileToggleBtn) mobileToggleBtn.addEventListener('click', toggleMobileSidebar);
        if(overlay) overlay.addEventListener('click', toggleMobileSidebar);
    });
</script>

@stack('scripts')
</body>
</html>
