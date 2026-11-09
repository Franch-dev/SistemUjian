<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Ujian CBT — SMK Negeri 1 Kota Bekasi</title>

    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/murid/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/murid/exam.css') }}">
</head>
<body class="exam-lock-body" oncontextmenu="return false;">

    <input type="hidden" id="activeIdUjianMurid" value="{{ $id_ujian_murid }}">

    <!-- OVERLAY LAYAR PENUH TERKUNCI (TANPA EMOTIKON) -->
    <div id="fullscreenBlockerOverlay" class="fullscreen-blocker-overlay">
        <div style="max-width:460px; background:#FFFFFF; border-radius:18px; padding:30px 26px; color:#0F172A; box-shadow:0 25px 60px rgba(0,0,0,0.35);">
            <span class="status-pill red" style="margin-bottom:12px;">Proteksi Layar Ujian</span>
            <h2 style="font-size:19px; font-weight:800; color:#0F172A; margin-bottom:8px;">
                Mode Layar Penuh Wajib Diaktifkan
            </h2>
            <p style="font-size:13px; color:#64748B; line-height:1.6; margin-bottom:22px;">
                Lembar soal disembunyikan sementara karena browser keluar dari mode Layar Penuh (Fullscreen). Klik tombol di bawah untuk kembali mengerjakan soal.
            </p>
            <button type="button" class="cbt-btn primary" style="width:100%; padding:13px;" onclick="aktifkanKembaliFullscreen()">
                Aktifkan Layar Penuh &amp; Lanjutkan Ujian
            </button>
        </div>
    </div>

    <!-- 1. TOP HEADER BAR RUANG UJIAN BERSIH -->
    <header class="exam-topbar">
        <div class="exam-brand-left">
            <div class="brand-shield-icon small">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3L4 6.5V12.5C4 17.2 7.4 21.4 12 22.5C16.6 21.4 20 17.2 20 12.5V6.5L12 3Z" fill="#2563EB"/>
                </svg>
            </div>
            <div>
                <h1 id="examHeaderMapel">Memuat Mata Pelajaran...</h1>
                <span id="examHeaderStudent">Peserta Ujian CBT — SMKN 1 Kota Bekasi</span>
            </div>
        </div>

        <div class="exam-status-right">
            <div class="violation-pill font-mono">
                Pelanggaran: <span id="violationCounterText">0 / 3</span>
            </div>
            <div id="examTimerBox" class="timer-countdown-box font-mono">
                00:00:00
            </div>
        </div>
    </header>

    <!-- 2. GRID UTAMA: LEMBAR SOAL KIRI & PANEL NOMOR SOAL KANAN -->
    <main class="exam-main-grid">

        <!-- KIRI: KARTU PERTANYAAN & OPSI A - E -->
        <section class="question-sheet-card">
            <div class="question-top-meta">
                <span id="currentNoBadge" class="question-number-badge font-mono">PERTANYAAN 1</span>
                <span id="autosaveStatusText" class="autosave-indicator">
                    Penyimpanan Otomatis Aktif
                </span>
            </div>

            <!-- Teks Pertanyaan -->
            <div id="questionStemText" class="question-stem-text">
                Memuat butir pertanyaan...
            </div>

            <!-- Gambar Pertanyaan (Jika Ada) -->
            <div id="questionStemImageBox" class="question-stem-image" style="display:none;">
                <img id="questionStemImg" src="" alt="Lampiran Gambar Soal">
            </div>

            <!-- Opsi Jawaban Pilihan Ganda A - E -->
            <div id="optionsContainer" class="options-choice-stack"></div>

            <!-- Navigasi Bawah Soal -->
            <div class="question-bottom-nav">
                <button type="button" id="btnPrevSoal" class="cbt-btn outline" onclick="navigasiSoal(-1)">
                    Soal Sebelumnya
                </button>

                <label class="ragu-toggle-label">
                    <input type="checkbox" id="chkStatusRagu" onchange="toggleRaguRaguSoalIni()" style="width:16px; height:16px; accent-color:#D97706;">
                    <span>Tandai Ragu-Ragu</span>
                </label>

                <button type="button" id="btnNextSoal" class="cbt-btn primary" onclick="navigasiSoal(1)">
                    Soal Selanjutnya
                </button>
            </div>
        </section>

        <!-- KANAN: PANEL DAFTAR NOMOR SOAL & PROGRES PENGERJAAN -->
        <aside class="number-nav-panel">
            <div class="nav-panel-header">
                <h3>Daftar Nomor Soal</h3>
                <span id="answeredProgressText" class="font-mono" style="font-size:12px; font-weight:700; color:#10B981;">0/0 Terjawab</span>
            </div>

            <div class="nav-progress-track">
                <div id="answeredProgressBar" class="nav-progress-fill"></div>
            </div>

            <div id="numberGridContainer" class="number-grid-5"></div>

            <!-- Legenda CSS Murni (Tanpa Emotikon) -->
            <div class="nav-legend-row">
                <span class="legend-item"><span class="legend-dot answered"></span> Terjawab</span>
                <span class="legend-item"><span class="legend-dot doubtful"></span> Ragu-ragu</span>
                <span class="legend-item"><span class="legend-dot empty"></span> Kosong</span>
            </div>

            <button type="button" class="cbt-btn primary" style="width:100%; padding:13px;" onclick="konfirmasiSubmitMandiri()">
                Selesai &amp; Kumpulkan Jawaban
            </button>
        </aside>

    </main>

    <script src="{{ asset('assets/js/murid/common.js') }}"></script>
    <script src="{{ asset('assets/js/murid/exam.js') }}"></script>
</body>
</html> 