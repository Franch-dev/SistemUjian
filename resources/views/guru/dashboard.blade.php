@extends('layouts.guru')

@section('title', 'Dashboard Guru Pengampu')
@section('page_heading', 'Ringkasan Kelas & Mata Pelajaran Ampuan')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/dashboard.css') }}">
@endpush

@section('content')
    <!-- Banner Sambutan Guru -->
    <div class="welcome-banner">
        <div class="welcome-left">
            <h2>Selamat datang, <span id="welcomeGuruName">Guru Pengampu</span></h2>
            <p>Kelola bank soal pilihan ganda, daftar hadir peserta, serta evaluasi nilai KKM kelas ampuan Anda</p>
        </div>
        <div class="welcome-right">
            <span class="session-running-pill">
                <span class="green-dot"></span>
                <span id="guruKodeBadge" class="font-mono">KODE: -</span>
            </span>
        </div>
    </div>

    <!-- 4 Kartu KPI Guru (Real-Time dari Database) -->
    <div class="dashboard-kpi-grid">
        <div class="dash-kpi-card">
            <div class="kpi-icon-square blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="16" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Total Kelas Ampuan</div>
            <div id="kpiGuruKelas" class="dash-kpi-number">0</div>
            <div class="dash-kpi-trend up-green">Sesuai plotting Admin</div>
        </div>

        <div class="dash-kpi-card">
            <div class="kpi-icon-square purple">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Total Butir Bank Soal</div>
            <div id="kpiGuruSoal" class="dash-kpi-number">0</div>
            <div class="dash-kpi-trend neutral">Soal Pilihan Ganda (A-E)</div>
        </div>

        <div class="dash-kpi-card">
            <div class="kpi-icon-square sky">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Total Murid Diampu</div>
            <div id="kpiGuruMurid" class="dash-kpi-number">0</div>
            <div class="dash-kpi-trend neutral">Siswa terdaftar di kelas</div>
        </div>

        <div class="dash-kpi-card">
            <div class="kpi-icon-square red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/>
                </svg>
            </div>
            <div class="dash-kpi-title">Sesi Ujian Terjadwal</div>
            <div id="kpiGuruSesi" class="dash-kpi-number">0</div>
            <div class="dash-kpi-trend neutral">Sesi ujian kelas ampuan</div>
        </div>
    </div>

    <!-- Daftar Kartu Kelas & Mata Pelajaran Ampuan -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Daftar Kelas &amp; Mata Pelajaran yang Anda Ampu</h3>
                <p>Klik tombol pintasan pada kelas untuk langsung mengelola Bank Soal atau melihat Rekap Nilai KKM</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Kelas &amp; Jurusan</th>
                        <th>Mata Pelajaran &amp; Kode</th>
                        <th>Standar KKM</th>
                        <th>Jumlah Murid</th>
                        <th>Bank Soal Tersedia</th>
                        <th>Pintasan Cepat</th>
                    </tr>
                </thead>
                <tbody id="tbodyKelasAmpuan"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/guru/dashboard.js') }}"></script>
@endpush