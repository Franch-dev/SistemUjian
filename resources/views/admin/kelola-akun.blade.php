@extends('layouts.admin')

@section('title', 'Kelola Akun Guru & Siswa')
@section('page_heading', 'Kelola Akun (Guru/Siswa)')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/pages.css') }}">
@endpush

@section('content')
    <!-- =================================================================
         PANEL 1: DAFTAR AKUN PESERTA DIDIK (MURID)
         ================================================================= -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Daftar Akun Peserta Didik (Murid)</h3>
                <p>Kelola akun siswa, edit kredensial, import/export Excel, dan cetak Kartu Peserta Ujian PDF</p>
            </div>
            <div class="btn-group-row">
                <button type="button" class="cbt-btn primary" onclick="bukaModalTambahMurid()">+ Tambah Murid</button>
                <label class="cbt-btn success">
                    Import Excel/CSV
                    <input type="file" hidden accept=".xlsx,.xls,.csv" onchange="importFileAkun(this, '/api/admin/murid/import')">
                </label>
                <button type="button" class="cbt-btn outline" onclick="cbtDownloadFile('/api/admin/murid/export?format=xlsx', 'data_murid.xlsx')">Export Excel</button>
                <button type="button" class="cbt-btn outline" onclick="cbtDownloadFile('/api/admin/cetak/kartu-peserta', 'kartu_peserta_ujian.pdf')">Cetak Kartu PDF</button>
            </div>
        </div>

        <!-- Bar Pencarian, Filter Kelas, & Sorting (Tanpa Form Tambah Inline) -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchMuridInput" class="cbt-input-sm search-input-wrap" placeholder="Cari nama atau NIS murid..." oninput="renderTabelMurid()">
                <select id="filterMuridKelas" class="cbt-input-sm" onchange="renderTabelMurid()">
                    <option value="">Semua Kelas</option>
                </select>
                <select id="sortMuridSelect" class="cbt-input-sm" onchange="renderTabelMurid()">
                    <option value="nama_asc">Urutkan: Nama (A - Z)</option>
                    <option value="nama_desc">Urutkan: Nama (Z - A)</option>
                    <option value="nis_asc">Urutkan: NIS Terkecil</option>
                    <option value="status_locked">Urutkan: Sesi Terkunci Dulu</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Password Kartu</th>
                        <th>Status Sesi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyMurid"></tbody>
            </table>
        </div>
    </div>

    <!-- =================================================================
         PANEL 2: DAFTAR AKUN GURU PENGAMPU
         ================================================================= -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Daftar Akun Guru Pengampu</h3>
                <p>Kode Guru (6 Huruf + 4 Angka) dibuat otomatis saat akun ditambahkan</p>
            </div>
            <div class="btn-group-row">
                <button type="button" class="cbt-btn primary" onclick="bukaModalTambahGuru()">+ Tambah Guru</button>
                <label class="cbt-btn success">
                    Import Guru Excel
                    <input type="file" hidden accept=".xlsx,.xls,.csv" onchange="importFileAkun(this, '/api/admin/guru/import')">
                </label>
                <button type="button" class="cbt-btn outline" onclick="cbtDownloadFile('/api/admin/guru/export?format=xlsx', 'data_guru.xlsx')">Export Guru Excel</button>
            </div>
        </div>

        <!-- Bar Pencarian & Sorting Guru -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchGuruInput" class="cbt-input-sm search-input-wrap" placeholder="Cari nama, Kode Guru, atau NIP..." oninput="renderTabelGuru()">
                <select id="sortGuruSelect" class="cbt-input-sm" onchange="renderTabelGuru()">
                    <option value="nama_asc">Urutkan: Nama Guru (A - Z)</option>
                    <option value="nama_desc">Urutkan: Nama Guru (Z - A)</option>
                    <option value="kode_asc">Urutkan: Kode Guru</option>
                    <option value="plotting_desc">Urutkan: Kelas Ampuan Terbanyak</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Kode Guru</th>
                        <th>Nama Lengkap</th>
                        <th>NIP</th>
                        <th>Plotting Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyGuru"></tbody>
            </table>
        </div>
    </div>

    <!-- =================================================================
         MODAL POP-UP FORM TAMBAH / EDIT MURID
         ================================================================= -->
    <div id="modalMurid" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4 id="modalMuridTitle">Tambah Akun Murid Baru</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalMurid')">✕</button>
            </div>
            <form id="formModalMurid" onsubmit="simpanDataMurid(event)">
                <input type="hidden" id="editMuridId">
                <div class="cbt-modal-body">
                    <div class="form-field-group">
                        <label>Nomor Induk Siswa (NIS)</label>
                        <input type="text" id="mNis" class="cbt-input-sm" placeholder="Contoh: 2022313601" required>
                    </div>
                    <div class="form-field-group">
                        <label>Nama Lengkap Murid</label>
                        <input type="text" id="mNama" class="cbt-input-sm" placeholder="Masukkan nama lengkap siswa" required>
                    </div>
                    <div class="form-field-group">
                        <label>Kelas / Rombel</label>
                        <select id="mKelas" class="cbt-input-sm" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>Password Login &amp; Kartu Ujian</label>
                        <div class="password-gen-row">
                            <input type="text" id="mPass" class="cbt-input-sm font-mono" placeholder="Kosongkan = otomatis sama dengan NIS">
                            <button type="button" class="cbt-btn edit-btn" onclick="isiPassMuridOtomatis()">Generate Acak</button>
                            <button type="button" class="cbt-btn outline" onclick="resetPassMuridKeNis()">Reset ke NIS</button>
                        </div>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalMurid')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Data Murid</button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================================
         MODAL POP-UP FORM TAMBAH / EDIT GURU
         ================================================================= -->
    <div id="modalGuru" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4 id="modalGuruTitle">Tambah Akun Guru Pengampu</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalGuru')">✕</button>
            </div>
            <form id="formModalGuru" onsubmit="simpanDataGuru(event)">
                <input type="hidden" id="editGuruId">
                <div class="cbt-modal-body">
                    <div class="form-field-group" id="groupKodeGuruInfo" style="display:none;">
                        <label>Kode Guru (Username Login)</label>
                        <input type="text" id="gKodeReadonly" class="cbt-input-sm font-mono" readonly style="background:#F1F5F9; color:#475569;">
                    </div>
                    <div class="form-field-group">
                        <label>Nama Lengkap &amp; Gelar Guru</label>
                        <input type="text" id="gNama" class="cbt-input-sm" placeholder="Contoh: Budi Santoso, S.Kom." required>
                    </div>
                    <div class="form-field-group">
                        <label>NIP (Nomor Induk Pegawai)</label>
                        <input type="text" id="gNip" class="cbt-input-sm font-mono" placeholder="Opsional (Contoh: 19850312...)">
                    </div>
                    <div class="form-field-group">
                        <label id="labelGuruPass">Password Login Guru</label>
                        <div class="password-gen-row">
                            <input type="text" id="gPass" class="cbt-input-sm font-mono" placeholder="Minimal 6 karakter">
                            <button type="button" class="cbt-btn edit-btn" onclick="generatePasswordGuru()">Generate / Reset Password</button>
                        </div>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalGuru')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Data Guru</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/akun.js') }}"></script>
@endpush