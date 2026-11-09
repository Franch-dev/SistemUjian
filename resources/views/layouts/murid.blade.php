<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Peserta Ujian CBT') — SMKN 1 Kota Bekasi</title>

    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/murid/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/murid/dashboard.css') }}">
    @stack('styles')
</head>
<body class="cbt-murid-body">

    <div id="mobileOverlay" class="mobile-sidebar-overlay" onclick="closeSidebarMobile()"></div>

    <!-- SIDEBAR PORTAL MURID -->
    <aside id="cbtSidebar" class="cbt-sidebar">
        <div class="sidebar-top-group">
            <div class="sidebar-brand-box">
                <div class="brand-shield-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M12 3L4 6.5V12.5C4 17.2 7.4 21.4 12 22.5C16.6 21.4 20 17.2 20 12.5V6.5L12 3Z" fill="#2563EB"/>
                        <path d="M12 6.5L7.5 8.5V12.2C7.5 15.1 9.4 17.7 12 18.5C14.6 17.7 16.5 15.1 16.5 12.2V8.5L12 6.5Z" fill="#93C5FD"/>
                    </svg>
                </div>
                <div class="brand-title-wrap">
                    <h2>SMKN 1 Kota Bekasi</h2>
                    <p>Portal Peserta CBT</p>
                </div>
            </div>

            <div class="device-lock-pill">
                <div class="device-lock-left">
                    <span class="green-dot"></span>
                    <span>Single Device Lock Aktif</span>
                </div>
            </div>

            <div class="sidebar-nav-heading">MENU PESERTA UJIAN</div>

            <nav class="sidebar-menu-links">
                <a href="{{ route('murid.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('murid.dashboard') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>Jadwal Ujian Aktif</span>
                    </span>
                </a>

                <a href="{{ route('murid.riwayat') }}"
                   class="sidebar-link {{ request()->routeIs('murid.riwayat') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <span>Riwayat &amp; Hasil Nilai</span>
                    </span>
                </a>
            </nav>
        </div>

        <div class="sidebar-bottom-user">
            <div class="user-info-row">
                <div class="user-avatar-blue" id="sidebarMuridInitial">MR</div>
                <div class="user-meta-text">
                    <h4 id="sidebarMuridName">Peserta Ujian</h4>
                    <p id="sidebarMuridNis" class="font-mono">NIS: -</p>
                </div>
            </div>
            <button type="button" class="btn-icon-logout" onclick="handleLogoutMurid()" title="Keluar (Logout)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </div>
    </aside>

    <!-- AREA KONTEN KANAN -->
    <div class="cbt-main-container">
        <header class="cbt-topbar">
            <div class="topbar-left-area">
                <button type="button" class="mobile-hamburger-btn" onclick="openSidebarMobile()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <div class="mobile-brand-inline">
                    <div class="brand-shield-icon small">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3L4 6.5V12.5C4 17.2 7.4 21.4 12 22.5C16.6 21.4 20 17.2 20 12.5V6.5L12 3Z" fill="#2563EB"/>
                        </svg>
                    </div>
                    <div>
                        <h3>SMKN 1 Kota Bekasi</h3>
                        <span>Portal Peserta CBT</span>
                    </div>
                </div>

                <h1 class="desktop-page-heading">@yield('page_heading', 'Portal Peserta Ujian')</h1>
            </div>

            <div class="topbar-right-area">
                <div class="topbar-pill desktop-only">
                    <span id="topbarAcademicYearText">Memuat TA...</span>
                </div>
                <div class="topbar-pill desktop-only">
                    <span id="topbarDateTimeText" class="font-mono">--:--:-- WIB</span>
                </div>
                <div class="topbar-pill desktop-only">
                    <span id="topbarMuridKelas">Kelas: -</span>
                </div>
                <div class="user-avatar-blue" id="topbarMuridInitial">MR</div>
            </div>
        </header>

        <main class="cbt-page-content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/js/murid/common.js') }}?v={{ time() }}"></script>
    @stack('scripts')
</body>
</html> 