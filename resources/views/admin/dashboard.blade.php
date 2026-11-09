@extends('layouts.admin')

@section('title', 'Dashboard Utama')
@section('page_heading', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/dashboard.css') }}">
@endpush

@section('content')
    <!-- 1. BANNER SELAMAT DATANG ADMINISTRATOR -->
    <div class="welcome-banner">
        <div class="welcome-left">
            <h2>Selamat datang, <span id="welcomeAdminName">Administrator</span></h2>
            <p>Ringkasan pelaksanaan asesmen hari ini (Data Real-Time Database)</p>
        </div>
        <div class="welcome-right">
            <span class="session-running-pill">
                <span class="green-dot"></span>
                <span id="bannerSesiStatusText">Memuat status sesi...</span>
            </span>
            <span id="lastUpdatedTime" class="last-updated-text">Terakhir diperbarui: --:-- WIB</span>
        </div>
    </div>

    <!-- 2. EMPAT KARTU KPI REAL-TIME -->
    <div class="dashboard-kpi-grid">
        <div class="dash-kpi-card">
            <div class="kpi-icon-square blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a4 4 0 0 1 4 4c0 1.5-.8 2.8-2 3.4V11h3a1 1 0 0 1 1 1v2h-2l1 5h2v2H5v-2h2l1-5H6v-2a1 1 0 0 1 1-1h3V9.4A4 4 0 0 1 8 6a4 4 0 0 1 4-4z"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Total Murid Terdaftar</div>
            <div id="kpiTotalMurid" class="dash-kpi-number">0</div>
            <div class="dash-kpi-trend up-green">Terdaftar di database</div>
        </div>

        <div class="dash-kpi-card">
            <div class="kpi-icon-square purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 2v7.31l-4.89 8.15A2 2 0 0 0 6.82 20h10.36a2 2 0 0 0 1.71-2.54L14 9.31V2"/>
                    <line x1="8.5" y1="2" x2="15.5" y2="2"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Guru &amp; Proktor</div>
            <div id="kpiTotalGuru" class="dash-kpi-number">0</div>
            <div id="kpiTotalPlottingSub" class="dash-kpi-trend neutral">0 Kelas ter-plotting</div>
        </div>

        <div class="dash-kpi-card">
            <div class="kpi-icon-square sky">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Sesi Ujian Aktif</div>
            <div id="kpiSesiAktif" class="dash-kpi-number">0</div>
            <div id="kpiMengerjakanSub" class="dash-kpi-trend neutral">0 Peserta sedang mengerjakan</div>
        </div>

        <div class="dash-kpi-card">
            <div class="kpi-icon-square red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Anti-Cheat Alert</div>
            <div id="kpiAntiCheat" class="dash-kpi-number danger-text">0</div>
            <div class="dash-kpi-trend up-red">Total insiden tercatat</div>
        </div>
    </div>

    <!-- 3. BARIS TENGAH: GRAFIK AKTIVITAS UJIAN & DONUT STATUS PESERTA (REAL DB) -->
    <div class="dashboard-split-row">
        <div class="dash-box-card">
            <div class="dash-card-header">
                <div>
                    <h3>Aktivitas Ujian</h3>
                    <p id="chartSubtitleText">Jumlah peserta mulai ujian per jam (Data Database Hari Ini vs Kemarin)</p>
                </div>
                <div class="time-filter-group">
                    <button type="button" class="filter-pill-btn active" onclick="switchChartPeriod('db_real', this)">Hari Ini (DB)</button>
                    <button type="button" class="filter-pill-btn" onclick="switchChartPeriod('simulasi_ramai', this)">Simulasi Hari 1 vs 2</button>
                </div>
            </div>

            <svg id="examActivitySvg" viewBox="0 0 600 220" width="100%" height="205">
                <defs>
                    <linearGradient id="chartAreaGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1D5DEC" stop-opacity="0.22"/>
                        <stop offset="100%" stop-color="#1D5DEC" stop-opacity="0.0"/>
                    </linearGradient>
                </defs>
                <g stroke="#F1F5F9" stroke-width="1">
                    <line x1="36" y1="20" x2="580" y2="20"/>
                    <line x1="36" y1="54" x2="580" y2="54"/>
                    <line x1="36" y1="88" x2="580" y2="88"/>
                    <line x1="36" y1="122" x2="580" y2="122"/>
                    <line x1="36" y1="156" x2="580" y2="156"/>
                    <line x1="36" y1="185" x2="580" y2="185"/>
                </g>
                <g id="chartYLabels" fill="#94A3B8" font-size="10" text-anchor="end"></g>
                <polyline id="chartLineDay2" fill="none" stroke="#94A3B8" stroke-width="2" stroke-dasharray="5,5" points=""/>
                <path id="chartAreaDay1" d="" fill="url(#chartAreaGrad)"/>
                <polyline id="chartLineDay1" fill="none" stroke="#1D5DEC" stroke-width="2.6" points=""/>
                <g id="chartPointsDay2" fill="#FFFFFF" stroke="#94A3B8" stroke-width="2"></g>
                <g id="chartPointsDay1" fill="#FFFFFF" stroke="#1D5DEC" stroke-width="2.2"></g>
                <g id="chartXLabels" fill="#94A3B8" font-size="10" text-anchor="middle"></g>
            </svg>

            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-top:6px; font-size:11.5px;">
                <div style="display:flex; align-items:center; gap:18px;">
                    <span style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:#0F172A;">
                        <span style="width:10px; height:10px; border-radius:50%; background:#1D5DEC; display:inline-block;"></span>
                        <span id="legendLabel1">Hari Ini (Database)</span>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; font-weight:600; color:#64748B;">
                        <span style="width:14px; height:2px; border-top:2px dashed #94A3B8; display:inline-block;"></span>
                        <span id="legendLabel2">Kemarin (Database)</span>
                    </span>
                </div>
                <span id="chartComparisonSummary" style="color:#10B981; font-weight:700;">Data real-time dari tabel ujian_murid</span>
            </div>
        </div>

        <!-- Status Peserta (Donut Chart Real-Time dari Database) -->
        <div class="dash-box-card">
            <div class="dash-card-header">
                <h3>Status Peserta (Real-Time)</h3>
            </div>

            <div class="donut-chart-wrapper">
                <svg width="146" height="146" viewBox="0 0 140 140">
                    <circle cx="70" cy="70" r="52" fill="none" stroke="#F1F5F9" stroke-width="14"/>
                    <circle id="donutArcSelesai" cx="70" cy="70" r="52" fill="none" stroke="#10B981" stroke-width="14" stroke-dasharray="0 327" stroke-dashoffset="0" transform="rotate(-90 70 70)"/>
                    <circle id="donutArcBerlangsung" cx="70" cy="70" r="52" fill="none" stroke="#1D5DEC" stroke-width="14" stroke-dasharray="0 327" stroke-dashoffset="0" transform="rotate(-90 70 70)"/>
                    <circle id="donutArcBelum" cx="70" cy="70" r="52" fill="none" stroke="#F59E0B" stroke-width="14" stroke-dasharray="327 327" stroke-dashoffset="0" transform="rotate(-90 70 70)"/>
                    <text id="donutCenterPct" x="70" y="68" text-anchor="middle" font-size="20" font-weight="800" fill="#0F172A">0%</text>
                    <text x="70" y="84" text-anchor="middle" font-size="11" fill="#64748B">Selesai</text>
                </svg>
            </div>

            <div class="donut-legend-list">
                <div class="donut-legend-row">
                    <span class="legend-name"><span class="dot-indicator green"></span> Selesai Ujian</span>
                    <div class="legend-values"><span id="statSelesaiCount">0</span><span id="statSelesaiPct" class="pct">0%</span></div>
                </div>
                <div class="donut-legend-row">
                    <span class="legend-name"><span class="dot-indicator blue"></span> Sedang Berlangsung</span>
                    <div class="legend-values"><span id="statBerlangsungCount">0</span><span id="statBerlangsungPct" class="pct">0%</span></div>
                </div>
                <div class="donut-legend-row">
                    <span class="legend-name"><span class="dot-indicator amber"></span> Belum Mulai</span>
                    <div class="legend-values"><span id="statBelumCount">0</span><span id="statBelumPct" class="pct">0%</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. BARIS BAWAH: DISTRIBUSI PESERTA PER JURUSAN & AKTIVITAS TERBARU (100% DATABASE) -->
    <div class="dashboard-split-row">
        <div class="dash-box-card">
            <div class="dash-card-header">
                <div>
                    <h3>Distribusi Peserta per Jurusan</h3>
                    <p>Perbandingan murid yang sudah mengikuti ujian vs total murid terdaftar per jurusan</p>
                </div>
            </div>

            <div id="jurusanDistributionContainer" class="jurusan-progress-list"></div>
        </div>

        <div class="dash-box-card">
            <div class="dash-card-header">
                <h3>Aktivitas Terbaru</h3>
                <a href="{{ route('admin.monitor') }}" style="font-size:12px; color:#1D5DEC; text-decoration:none; font-weight:700;">Lihat Live Monitor</a>
            </div>

            <div id="recentActivityList" class="activity-feed-list"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>
@endpush