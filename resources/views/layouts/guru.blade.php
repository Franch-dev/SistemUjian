<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Guru Pengampu') — SMKN 1 Kota Bekasi</title>

    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/guru/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/guru/pages.css') }}">
    @stack('styles')
</head>
<body class="cbt-guru-body">

    <div id="mobileOverlay" class="mobile-sidebar-overlay" onclick="closeSidebarMobile()"></div>

    <!-- SIDEBAR NAVIGASI GURU PENGAMPU -->
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
                    <p>Portal Guru Pengampu</p>
                </div>
            </div>

            <div class="server-online-pill">
                <div class="server-online-left">
                    <span class="green-dot"></span>
                    <span>Sinkronisasi Bank Soal</span>
                </div>
                <span class="server-ping-text font-mono">Aktif</span>
            </div>

            <div class="sidebar-nav-heading">MENU GURU PENGAMPU</div>

            <nav class="sidebar-menu-links">
                <a href="{{ route('guru.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1V9.5z"/>
                        </svg>
                        <span>Kelas &amp; Mapel Ampuan</span>
                    </span>
                </a>

                <a href="{{ route('guru.bank-soal') }}"
                   class="sidebar-link {{ request()->routeIs('guru.bank-soal') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <span>Manajemen Bank Soal</span>
                    </span>
                </a>

                <a href="{{ route('guru.daftar-hadir') }}"
                   class="sidebar-link {{ request()->routeIs('guru.daftar-hadir') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1"/>
                        </svg>
                        <span>Daftar Hadir &amp; Berita Acara</span>
                    </span>
                </a>

                <a href="{{ route('guru.rekap-nilai') }}"
                   class="sidebar-link {{ request()->routeIs('guru.rekap-nilai') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                        <span>Rekap Nilai &amp; Log Cheat</span>
                    </span>
                </a>
            </nav>
        </div>

        <div class="sidebar-bottom-user">
            <div class="user-info-row">
                <div class="user-avatar-blue" id="sidebarGuruInitial">GR</div>
                <div class="user-meta-text">
                    <h4 id="sidebarGuruName">Guru Pengampu</h4>
                    <p id="sidebarGuruKode" class="font-mono">KODE: -</p>
                </div>
            </div>
            <button type="button" class="btn-icon-logout" onclick="handleLogoutGuru()" title="Keluar (Logout)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA KANAN -->
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
                        <span>Portal Guru CBT</span>
                    </div>
                </div>

                <h1 class="desktop-page-heading">@yield('page_heading', 'Dashboard Guru Pengampu')</h1>
            </div>

            <div class="topbar-right-area">
                <div class="topbar-pill desktop-only">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span id="topbarAcademicYearText">Memuat TA...</span>
                </div>
                <div class="topbar-pill desktop-only">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/>
                    </svg>
                    <span id="topbarDateTimeText" class="font-mono">-- --- ---- • --:--:-- WIB</span>
                </div>
                <div class="user-avatar-blue topbar-avatar" id="topbarGuruInitial">GR</div>
            </div>
        </header>

        <main class="cbt-page-content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/js/guru/common.js') }}?v={{ time() }}"></script>
    @stack('scripts')
</body>
</html>