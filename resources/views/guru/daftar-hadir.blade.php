@extends('layouts.guru')

@section('title', 'Daftar Hadir & Berita Acara')
@section('page_heading', 'Daftar Hadir Peserta & Berita Acara Ujian')

@section('content')
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Daftar Hadir &amp; Berita Acara Pelaksanaan Ujian Kelas Ampuan</h3>
                <p>Pantau kehadiran murid pada sesi ujian dan cetak berkas PDF resmi ber-kop SMK Negeri 1 Kota Bekasi</p>
            </div>
            <button type="button" class="cbt-btn primary" onclick="cetakPdfDaftarHadirGuru()">
                Cetak Daftar Hadir &amp; Berita Acara (PDF)
            </button>
        </div>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <select id="selectPlottingHadir" class="cbt-input-sm" onchange="muatDaftarHadirKelas()" style="min-width:240px; font-weight:700;"></select>
                <select id="selectSesiHadir" class="cbt-input-sm" style="min-width:220px;"></select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Lengkap Murid</th>
                        <th>Status Kehadiran CBT</th>
                        <th>Jumlah Pelanggaran</th>
                        <th>Waktu Mulai Ujian</th>
                    </tr>
                </thead>
                <tbody id="tbodyDaftarHadir"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/guru/daftar-hadir.js') }}"></script>
@endpush