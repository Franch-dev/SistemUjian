@extends('layouts.guru')

@section('title', 'Manajemen Bank Soal PG')
@section('page_heading', 'Manajemen Formulir & Bank Soal Pilihan Ganda')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/guru/bank-soal.css') }}?v={{ time() }}">
@endpush

@section('content')

    <!-- =================================================================
         TAMPILAN 1: DAFTAR FORMULIR UJIAN (BISA BANYAK FORM PER MAPEL/KELAS)
         ================================================================= -->
    <div id="mainPackageListView">
        <div class="page-card">
            <div class="page-card-header">
                <div>
                    <h3>Daftar Formulir Ujian Kelas Ampuan</h3>
                    <p>Anda dapat membuat banyak formulir ujian berbeda (misal: Kuis, UTS, UAS, Susulan) untuk mata pelajaran dan kelas yang sama</p>
                </div>
                <div class="btn-group-row">
                    <button type="button" class="cbt-btn primary" onclick="bukaModalBuatFormBaru()">
                        + Buat Formulir Ujian Baru
                    </button>
                </div>
            </div>

            <!-- Bar Pencarian & Filter Formulir -->
            <div class="table-toolbar">
                <div class="toolbar-left">
                    <input type="text" id="searchPackageInput" class="cbt-input-sm search-input-wrap" placeholder="Cari judul formulir, mata pelajaran, atau kelas..." oninput="renderDaftarPaketFormulir()">
                    <select id="filterPackageJurusan" class="cbt-input-sm" onchange="renderDaftarPaketFormulir()">
                        <option value="">Semua Jurusan Ampuan</option>
                    </select>
                </div>
            </div>

            <!-- Daftar Kartu Formulir Ujian (Klik Judul -> Expand Daftar Soal) -->
            <div id="packageAccordionContainer" class="gform-package-list"></div>
        </div>
    </div>

    <!-- =================================================================
         TAMPILAN 2: KANVAS GOOGLE FORM BUILDER
         ================================================================= -->
    <div id="googleFormBuilderView" class="gform-canvas-bg" style="display: none;">
        <div class="gform-workspace-container">

            <!-- Kartu Header Formulir (Judul Formulir Bisa Diedit Langsung) -->
            <div class="gform-header-card">
                <div class="gform-header-top-actions">
                    <span id="gfBadgeKelasJurusan" class="status-pill blue">XII RPL 1</span>
                    <div class="btn-group-row">
                        <button type="button" class="cbt-btn primary" onclick="tambahKartuPertanyaanBaru()">
                            + Tambah Pertanyaan
                        </button>
                        <button type="button" class="cbt-btn edit-btn" onclick="hitungBobotOtomatis100(true)">
                            Bagi Rata Bobot (Total 100)
                        </button>
                        <button type="button" id="btnHapusFormDiEditor" class="cbt-btn danger" onclick="hapusFormulirAktif()">
                            Hapus Formulir Ini
                        </button>
                        <button type="button" class="cbt-btn outline" onclick="batalEditSoalGoogleForm()">
                            Batal
                        </button>
                    </div>
                </div>

                <!-- Input Judul Formulir Ujian ala Google Form -->
                <div style="margin-bottom:8px;">
                    <input type="text" id="gfFormTitleInput"
                        style="width:100%; border:none; border-bottom:2px solid #4F46E5; font-family:inherit; font-size:24px; font-weight:800; color:#0F172A; padding:6px 0; outline:none; background:transparent;"
                        placeholder="Judul Formulir Ujian">
                </div>
                <div id="gfFormSubtitleMapel" style="font-size:13px; font-weight:700; color:#1D5DEC; margin-bottom:6px;">
                    Mata Pelajaran: -
                </div>
                <div class="gform-desc-display">
                    Tambahkan pertanyaan pilihan ganda (Opsi A s/d E). Anda dapat menyisipkan gambar pada pertanyaan maupun pada masing-masing opsi jawaban. Bobot nilai seluruh soal dalam formulir ini otomatis disesuaikan menjadi <strong>100 Poin</strong>.
                </div>
            </div>

            <!-- Container Kartu Pertanyaan 1 s/d N -->
            <div id="gfQuestionsListContainer"></div>

            <!-- Toolbar Melayang di Samping Kanan -->
            <div class="gform-side-floating-menu">
                <button type="button" class="gform-side-btn" onclick="tambahKartuPertanyaanBaru()" title="Tambah Pertanyaan Baru (+)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </button>
                <button type="button" class="gform-side-btn" onclick="hitungBobotOtomatis100(true)" title="Bagi Rata Bobot 100 Poin">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v18M3 9l9-4 9 4M5 15l-2 4h6l-2-4zm14 0l-2 4h6l-2-4z"/>
                    </svg>
                </button>
                <button type="button" class="gform-side-btn" onclick="batalEditSoalGoogleForm()" title="Batal Edit Soal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </button>
                <button type="button" class="gform-side-btn" onclick="simpanSeluruhFormSoal()" title="Simpan Formulir Ujian">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.4">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                    </svg>
                </button>
            </div>

            <!-- Bar Simpan & Batal Melayang di Bawah -->
            <div class="gform-bottom-save-bar">
                <button type="button" class="cbt-btn edit-btn" onclick="tambahKartuPertanyaanBaru()">
                    + Tambah Pertanyaan
                </button>

                <div style="font-size:13px; font-weight:700; color:#334155;">
                    Jumlah Soal: <span id="gfTotalSoalCount" class="font-mono" style="color:#1D5DEC;">0</span> &nbsp;|&nbsp;
                    Total Bobot: <span id="gfTotalBobotCount" class="font-mono" style="color:#10B981;">100</span> / 100 Poin
                </div>

                <div style="display:flex; gap:8px;">
                    <button type="button" class="cbt-btn outline" onclick="batalEditSoalGoogleForm()">
                        Batal
                    </button>
                    <button type="button" class="cbt-btn primary" onclick="simpanSeluruhFormSoal()">
                        Simpan Formulir &amp; Soal
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- =================================================================
         MODAL POP-UP BUAT FORMULIR UJIAN BARU (MULAI DARI KOSONG / BARU)
         ================================================================= -->
    <div id="modalPilihTargetSoal" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4>Buat Formulir Ujian Baru</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalPilihTargetSoal')">✕</button>
            </div>
            <form onsubmit="mulaiBuatFormulirBaru(event)">
                <div class="cbt-modal-body">
                    <div class="form-field-group">
                        <label>1. Filter Kompetensi Keahlian (Jurusan)</label>
                        <select id="modalSelectJurusan" class="cbt-input-sm" onchange="filterKelasDiModalTarget()" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>2. Pilih Kelas &amp; Mata Pelajaran Ampuan</label>
                        <select id="modalSelectPlotting" class="cbt-input-sm" onchange="isiJudulFormulirDefault()" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>3. Judul Formulir Ujian Baru</label>
                        <input type="text" id="modalJudulFormulirBaru" class="cbt-input-sm" placeholder="Contoh: Ujian Harian 1 — Pengembangan Teks Grafik Multimedia" required>
                    </div>
                    <div class="form-field-group">
                        <label>4. Jumlah Kartu Soal untuk Formulir Baru Ini</label>
                        <input type="number" id="modalTargetJumlahSoal" class="cbt-input-sm" value="5" min="1" max="100" required>
                        <small style="color:#64748B; font-size:11.5px; margin-top:4px; display:block;">
                            Sistem akan membuat <strong>formulir ujian baru yang terpisah dari formulir sebelumnya</strong> dengan bobot nilai otomatis dibagi rata menjadi 100 Poin.
                        </small>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalPilihTargetSoal')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Buat Formulir Ujian Baru →</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/guru/bank-soal.js') }}?v={{ time() }}"></script>
@endpush