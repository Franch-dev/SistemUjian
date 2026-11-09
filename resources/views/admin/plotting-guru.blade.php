@extends('layouts.admin')

@section('title', 'Plotting Guru Pengampu')
@section('page_heading', 'Plotting Guru Pengampu')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/pages.css') }}">
@endpush

@section('content')
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Pemetaan (Plotting) Guru Pengampu per Mata Pelajaran &amp; Kelas</h3>
                <p>Menghubungkan guru dengan kelas dan mata pelajaran untuk pengelolaan bank soal</p>
            </div>
            <button type="button" class="cbt-btn primary" onclick="openModal('modalPlotting')">+ Tambah Plotting Guru</button>
        </div>

        <!-- Bar Pencarian, Filter Kelas, & Sorting -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchPlotting" class="cbt-input-sm search-input-wrap" placeholder="Cari nama guru atau mata pelajaran..." oninput="renderPlotting()">
                <select id="filterPlottingKelas" class="cbt-input-sm" onchange="renderPlotting()">
                    <option value="">Semua Kelas</option>
                </select>
                <select id="sortPlotting" class="cbt-input-sm" onchange="renderPlotting()">
                    <option value="guru_asc">Urutkan: Nama Guru (A - Z)</option>
                    <option value="mapel_asc">Urutkan: Mata Pelajaran (A - Z)</option>
                    <option value="soal_desc">Urutkan: Bank Soal Terbanyak</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Guru Pengampu</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Jumlah Soal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyPlotting"></tbody>
            </table>
        </div>
    </div>

    <!-- MODAL POP-UP TAMBAH PLOTTING -->
    <div id="modalPlotting" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4>Tetapkan Plotting Guru Pengampu Baru</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalPlotting')">✕</button>
            </div>
            <form onsubmit="simpanPlotting(event)">
                <div class="cbt-modal-body">
                    <div class="form-field-group">
                        <label>Pilih Guru Pengampu</label>
                        <select id="pGuru" class="cbt-input-sm" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>Pilih Mata Pelajaran</label>
                        <select id="pMapel" class="cbt-input-sm" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>Pilih Kelas / Rombel</label>
                        <select id="pKelas" class="cbt-input-sm" required></select>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalPlotting')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Plotting</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/plotting.js') }}"></script>
@endpush