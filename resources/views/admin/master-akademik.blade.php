@extends('layouts.admin')

@section('title', 'Master Akademik & Mapel')
@section('page_heading', 'Master Akademik & Mapel')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/pages.css') }}">
@endpush

@section('content')
    <!-- 1. MASTER JURUSAN -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>1. Master Kompetensi Keahlian (Jurusan)</h3>
                <p>Daftar jurusan resmi SMK Negeri 1 Kota Bekasi</p>
            </div>
            <button type="button" class="cbt-btn primary" onclick="bukaModalJurusan()">+ Tambah Jurusan</button>
        </div>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchJurusan" class="cbt-input-sm search-input-wrap" placeholder="Cari kode atau nama jurusan..." oninput="renderJurusan()">
                <select id="sortJurusan" class="cbt-input-sm" onchange="renderJurusan()">
                    <option value="kode_asc">Urutkan: Kode (A - Z)</option>
                    <option value="nama_asc">Urutkan: Nama Jurusan (A - Z)</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Kode Jurusan</th>
                        <th>Nama Kompetensi Keahlian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyJurusan"></tbody>
            </table>
        </div>
    </div>

    <!-- 2. MASTER KELAS -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>2. Master Kelas &amp; Rombel</h3>
                <p>Kelola rombongan belajar per tingkat (X, XI, XII)</p>
            </div>
            <button type="button" class="cbt-btn primary" onclick="bukaModalKelas()">+ Tambah Kelas</button>
        </div>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchKelas" class="cbt-input-sm search-input-wrap" placeholder="Cari nama kelas..." oninput="renderKelas()">
                <select id="sortKelas" class="cbt-input-sm" onchange="renderKelas()">
                    <option value="nama_asc">Urutkan: Nama Kelas (A - Z)</option>
                    <option value="murid_desc">Urutkan: Siswa Terbanyak</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Jurusan</th>
                        <th>Jumlah Murid</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyKelas"></tbody>
            </table>
        </div>
    </div>

    <!-- 3. MASTER MAPEL & KKM -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>3. Master Mata Pelajaran, Jurusan, Tingkat Kelas &amp; Standar KKM</h3>
                <p>Kode mapel otomatis memuat kode jurusan dan tingkat kelas (X / XI / XII)</p>
            </div>
            <button type="button" class="cbt-btn primary" onclick="bukaModalMapel()">+ Tambah Mapel</button>
        </div>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchMapel" class="cbt-input-sm search-input-wrap" placeholder="Cari nama mapel, jurusan, atau tingkat..." oninput="renderMapel()">
                <select id="filterMapelJurusan" class="cbt-input-sm" onchange="renderMapel()">
                    <option value="">Semua Jurusan &amp; Umum</option>
                    <option value="UMUM">Mapel Umum (Nasional)</option>
                </select>
                <select id="filterMapelTingkat" class="cbt-input-sm" onchange="renderMapel()">
                    <option value="">Semua Tingkat (X, XI, XII)</option>
                    <option value="X">Khusus Kelas X</option>
                    <option value="XI">Khusus Kelas XI</option>
                    <option value="XII">Khusus Kelas XII</option>
                </select>
                <select id="sortMapel" class="cbt-input-sm" onchange="renderMapel()">
                    <option value="nama_asc">Urutkan: Nama Mapel (A - Z)</option>
                    <option value="kkm_desc">Urutkan: KKM Tertinggi</option>
                    <option value="kkm_asc">Urutkan: KKM Terendah</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Kode Mapel</th>
                        <th>Jurusan &amp; Tingkat</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Nilai KKM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyMapel"></tbody>
            </table>
        </div>
    </div>

    <!-- MODAL JURUSAN -->
    <div id="modalJurusan" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4 id="titleModalJurusan">Tambah Jurusan</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalJurusan')">✕</button>
            </div>
            <form onsubmit="simpanJurusan(event)">
                <input type="hidden" id="editJurusanId">
                <div class="cbt-modal-body">
                    <div class="form-field-group">
                        <label>Kode Singkatan Jurusan</label>
                        <input type="text" id="jKode" class="cbt-input-sm" placeholder="Contoh: RPL / TKJ" required>
                    </div>
                    <div class="form-field-group">
                        <label>Nama Lengkap Kompetensi Keahlian</label>
                        <input type="text" id="jNama" class="cbt-input-sm" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalJurusan')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Jurusan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KELAS -->
    <div id="modalKelas" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4 id="titleModalKelas">Tambah Kelas Baru</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalKelas')">✕</button>
            </div>
            <form onsubmit="simpanKelas(event)">
                <input type="hidden" id="editKelasId">
                <div class="cbt-modal-body">
                    <div class="form-field-group">
                        <label>Tingkat Kelas</label>
                        <select id="kTingkat" class="cbt-input-sm" required>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <div class="form-field-group">
                        <label>Jurusan</label>
                        <select id="kJurusan" class="cbt-input-sm" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>Nomor Rombel</label>
                        <input type="text" id="kRombel" class="cbt-input-sm" placeholder="Contoh: 1 atau 2" required>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalKelas')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MAPEL (DENGAN KUNCI PREFIX JURUSAN & TINGKAT OTOMATIS) -->
    <div id="modalMapel" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4 id="titleModalMapel">Tambah Mata Pelajaran Baru</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalMapel')">✕</button>
            </div>
            <form onsubmit="simpanMapel(event)">
                <input type="hidden" id="editMapelId">
                <div class="cbt-modal-body">
                    <div class="form-field-group">
                        <label>Peruntukan Jurusan</label>
                        <select id="mpJurusanKode" class="cbt-input-sm" onchange="updatePreviewKodeMapel()" required>
                            <option value="UMUM">UMUM (Semua Jurusan — MTK, B.Indo, dll)</option>
                        </select>
                    </div>
                    <div class="form-field-group">
                        <label>Peruntukan Tingkat Kelas</label>
                        <select id="mpTingkat" class="cbt-input-sm" onchange="updatePreviewKodeMapel()" required>
                            <option value="ALL">Semua Tingkat (X, XI, XII)</option>
                            <option value="X">Khusus Kelas X</option>
                            <option value="XI">Khusus Kelas XI</option>
                            <option value="XII">Khusus Kelas XII</option>
                        </select>
                    </div>
                    <div class="form-field-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" id="mpNama" class="cbt-input-sm" placeholder="Contoh: Pengembangan Website" oninput="isiSingkatanOtomatis()" required>
                    </div>
                    <div class="form-field-group">
                        <label>Singkatan Kode Mapel (Misal: PEMWEB / BD01)</label>
                        <div class="password-gen-row" style="align-items:center;">
                            <span id="mpPrefixBadge" class="status-pill blue font-mono" style="padding:9px 12px; font-size:12px; white-space:nowrap;">UMUM-ALL-</span>
                            <input type="text" id="mpSingkatan" class="cbt-input-sm font-mono" placeholder="PEMWEB" oninput="updatePreviewKodeMapel()" required>
                        </div>
                        <small style="color:#64748B; font-size:11px; margin-top:4px; display:block;">
                            Kode lengkap yang akan disimpan: <strong id="mpKodeFullPreview" class="font-mono" style="color:#1D5DEC;">UMUM-ALL-PEMWEB</strong>
                        </small>
                        <input type="hidden" id="mpKode">
                    </div>
                    <div class="form-field-group">
                        <label>Standar Nilai KKM (0 - 100)</label>
                        <input type="number" id="mpKkm" class="cbt-input-sm" value="75" min="0" max="100" required>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalMapel')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/master.js') }}"></script>
@endpush