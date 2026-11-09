<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Admin CBT') — SMKN 1 Kota Bekasi</title>

    <!-- CSS Global & Layout Sidebar/Topbar -->
    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/layout.css') }}">

    <!-- CSS Khusus Masing-Masing Halaman -->
    @stack('styles')
</head>
<body class="cbt-admin-body">

    <!-- Overlay Gelap saat Hamburger Menu HP Dibuka -->
    <div id="mobileOverlay" class="mobile-sidebar-overlay" onclick="closeSidebarMobile()"></div>

    <!-- SIDEBAR KIRI ADMIN -->
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
                    <p>Portal Admin CBT</p>
                </div>
            </div>

            <div class="server-online-pill">
                <div class="server-online-left">
                    <span class="green-dot"></span>
                    <span>Server CBT Online</span>
                </div>
                <span class="server-ping-text font-mono">1.2 ms</span>
            </div>

            <div class="sidebar-nav-heading">NAVIGASI UTAMA</div>

            <nav class="sidebar-menu-links">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1V9.5z"/>
                        </svg>
                        <span>Dashboard Utama</span>
                    </span>
                </a>

                <a href="{{ route('admin.akun') }}"
                   class="sidebar-link {{ request()->routeIs('admin.akun') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>Kelola Akun (Guru/Siswa)</span>
                    </span>
                </a>

                <a href="{{ route('admin.reset') }}"
                   class="sidebar-link {{ request()->routeIs('admin.reset') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                        <span>Reset Sesi Terkunci</span>
                    </span>
                    <span id="sidebarLockedBadge" class="badge-count-amber">0</span>
                </a>

                <a href="{{ route('admin.master') }}"
                   class="sidebar-link {{ request()->routeIs('admin.master') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="7" width="18" height="13" rx="2"/>
                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        <span>Master Akademik &amp; Mapel</span>
                    </span>
                </a>

                <a href="{{ route('admin.plotting') }}"
                   class="sidebar-link {{ request()->routeIs('admin.plotting') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="8" y="8" width="12" height="12" rx="2"/>
                            <path d="M4 16V6a2 2 0 0 1 2-2h10"/>
                        </svg>
                        <span>Plotting Guru Pengampu</span>
                    </span>
                </a>

                <a href="{{ route('admin.monitor') }}"
                   class="sidebar-link {{ request()->routeIs('admin.monitor') ? 'active' : '' }}">
                    <span class="link-left">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="12" rx="2"/>
                            <path d="M8 20h8M12 16v4"/>
                        </svg>
                        <span>Kontrol &amp; Live Monitor</span>
                    </span>
                    <span class="badge-live-red">Live</span>
                </a>
            </nav>
        </div>

        <div class="sidebar-bottom-user">
            <a href="{{ route('admin.profil') }}" class="user-info-row" style="text-decoration:none;" title="Klik untuk Edit Profil & Password Admin">
                <div class="user-avatar-blue" id="sidebarAvatarInitial">AD</div>
                <div class="user-meta-text">
                    <h4 id="sidebarAdminName">Administrator</h4>
                    <p>Klik untuk Edit Profil</p>
                </div>
            </a>
            <button type="button" class="btn-icon-logout" onclick="handleLogoutAdmin()" title="Keluar (Logout)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </div>
    </aside>

    <!-- WRAPPER UTAMA KANAN -->
    <div class="cbt-main-container">
        <header class="cbt-topbar">
            <div class="topbar-left-area">
                <button type="button" class="mobile-hamburger-btn" onclick="openSidebarMobile()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
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
                        <span>Portal Admin CBT</span>
                    </div>
                </div>

                <h1 class="desktop-page-heading">@yield('page_heading', 'Dashboard')</h1>
            </div>

            <div class="topbar-right-area">
                <!-- Pill Tahun Ajaran Otomatis Kalender Pendidikan Indonesia -->
                <div class="topbar-pill desktop-only">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span id="topbarAcademicYearText">Memuat TA...</span>
                </div>

                <!-- Pill Tanggal & Jam Real-Time WIB -->
                <div class="topbar-pill desktop-only">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/>
                    </svg>
                    <span id="topbarDateTimeText" class="font-mono">-- --- ---- • --:--:-- WIB</span>
                </div>

                <div class="topbar-search desktop-only">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" placeholder="Cari siswa, sesi...">
                </div>

                <button type="button" class="notif-bell-btn">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="1.9">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notif-red-dot"></span>
                </button>

                <a href="{{ route('admin.profil') }}" class="user-avatar-blue topbar-avatar" id="topbarAvatarInitial" style="text-decoration:none;" title="Pengaturan Akun Administrator">AD</a>
            </div>
        </header>

        <main class="cbt-page-content">
            @yield('content')
        </main>
    </div>

    <!-- JS Global Admin -->
    <script src="{{ asset('assets/js/admin/common.js') }}?v={{ time() }}"></script>
    @stack('scripts')
</body>
</html>