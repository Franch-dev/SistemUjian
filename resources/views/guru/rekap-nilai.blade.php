@extends('layouts.guru')

@section('title', 'Rekap Nilai & Log Pelanggaran')
@section('page_heading', 'Rekapitulasi Nilai KKM & Log Riwayat Pelanggaran')

@section('content')
    <!-- Panel 1: Rekapitulasi Nilai & Status Lulus / Remedial -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Rekapitulasi Nilai Murid &amp; Evaluasi Standar KKM</h3>
                <p>Status kelulusan otomatis membandingkan Nilai Akhir siswa dengan Nilai KKM Mata Pelajaran</p>
            </div>
            <div class="btn-group-row">
                <button type="button" class="cbt-btn success" onclick="unduhRekapExcelGuru()">Unduh Rekap Nilai (.XLSX)</button>
                <button type="button" class="cbt-btn warning" onclick="unduhLogCheatExcelGuru()">Unduh Log Pelanggaran (.XLSX)</button>
            </div>
        </div>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <select id="selectPlottingRekap" class="cbt-input-sm" onchange="muatRekapDanLog()" style="min-width:240px; font-weight:700;"></select>
                <input type="text" id="searchRekapMurid" class="cbt-input-sm search-input-wrap" placeholder="Cari nama atau NIS murid..." oninput="renderTabelRekap()">
                <select id="filterKelulusan" class="cbt-input-sm" onchange="renderTabelRekap()">
                    <option value="">Semua Status (Lulus &amp; Remedial)</option>
                    <option value="Lulus">Hanya yang LULUS KKM</option>
                    <option value="Remedial">Hanya yang REMEDIAL</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Lengkap Murid</th>
                        <th>Standar KKM</th>
                        <th>Jawaban Benar</th>
                        <th>Nilai Akhir</th>
                        <th>Status Kelulusan</th>
                        <th>Status Penyelesaian</th>
                    </tr>
                </thead>
                <tbody id="tbodyRekapNilai"></tbody>
            </table>
        </div>
    </div>

    <!-- Panel 2: Riwayat Log Pelanggaran Anti-Cheat Kelas Ampuan -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Riwayat Log Pelanggaran Anti-Cheat Kelas Ampuan</h3>
                <p>Daftar insiden keluar fullscreen atau pindah tab browser yang tercatat pada mata pelajaran ini</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Waktu Kejadian</th>
                        <th>NIS</th>
                        <th>Nama Murid</th>
                        <th>Jenis Pelanggaran Anti-Cheat</th>
                        <th>Total Pelanggaran</th>
                    </tr>
                </thead>
                <tbody id="tbodyLogPelanggaranGuru"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/guru/rekap-nilai.js') }}"></script>
@endpush