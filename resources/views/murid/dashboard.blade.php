@extends('layouts.murid')

@section('title', 'Jadwal Ujian Aktif')
@section('page_heading', 'Jadwal & Pelaksanaan Ujian CBT')

@section('content')
    <!-- 1. BANNER SAMBUTAN BERSIH -->
    <div class="student-welcome-banner">
        <div>
            <h2>Selamat datang, <span id="welcomeStudentName">Peserta Ujian</span></h2>
            <p>Pilih hari pelaksanaan di bawah ini untuk melihat daftar mata pelajaran ujian kelas Anda.</p>
        </div>
        <span id="welcomeStudentClass" class="student-class-badge">Kelas: -</span>
    </div>

    <!-- 2. BANNER RESUME UJIAN (JIKA ADA UJIAN SEDANG BERJALAN) -->
    <div id="resumeExamBanner" class="resume-alert-card">
        <div>
            <h4 style="font-size:14.5px; font-weight:800; color:#92400E;">Sesi Ujian Sedang Berlangsung</h4>
            <p id="resumeExamDesc" style="font-size:12.5px; color:#B45309; margin-top:2px;">
                Sisa waktu dan jawaban Anda telah tersimpan otomatis. Silakan lanjutkan pengerjaan ujian Anda.
            </p>
        </div>
        <button type="button" id="btnResumeDirect" class="cbt-btn warning">
            Lanjutkan Pengerjaan Ujian
        </button>
    </div>

    <!-- 3. NAVIGASI JADWAL UJIAN PER HARI (SENIN - JUMAT) -->
    <div class="schedule-day-section">
        <div class="schedule-day-header">
            <div>
                <h3>Jadwal Pelaksanaan Ujian Mingguan (Senin – Jumat)</h3>
                <p id="selectedDaySummaryText">Menampilkan seluruh jadwal ujian yang terdaftar untuk kelas Anda</p>
            </div>
        </div>

        <div id="studentDayTabsContainer" class="student-day-tabs"></div>
    </div>

    <!-- 4. GRID KARTU MATA PELAJARAN UJIAN -->
    <div id="scheduleCardsContainer" class="exam-schedule-grid"></div>

    <!-- 5. MODAL PAKTA INTEGRITAS (BERSIH TANPA EMOTIKON) -->
    <div id="modalPaktaIntegritas" class="cbt-modal-backdrop">
        <div class="cbt-modal-box" style="max-width: 540px;">
            <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center;">
                <h4 style="font-size:16px; font-weight:800; color:#0F172A;">Pakta Integritas &amp; Ketentuan Ujian CBT</h4>
                <button type="button" onclick="closeModal('modalPaktaIntegritas')" style="background:#F8FAFC; border:1px solid #E2E8F0; width:30px; height:30px; border-radius:8px; cursor:pointer; font-weight:700;">✕</button>
            </div>

            <form onsubmit="konfirmasiMulaiUjian(event)">
                <input type="hidden" id="selectedIdSesi">
                <div style="padding:20px 22px; display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <span class="status-pill blue" id="paktaDurasiBadge">Durasi: 90 Menit</span>
                        <h3 id="paktaNamaMapel" style="font-size:17px; font-weight:800; color:#0F172A; margin-top:8px;">Nama Mata Pelajaran</h3>
                    </div>

                    <div class="pakta-rules-box">
                        <strong>Ketentuan Pelaksanaan &amp; Keamanan Sistem Anti-Cheat:</strong>
                        <ul>
                            <li>Ujian dilaksanakan dalam mode <strong>Layar Penuh Terkunci (Fullscreen Lock)</strong>.</li>
                            <li>Peserta dilarang berpindah tab browser, menutup jendela ujian, atau keluar dari mode layar penuh selama waktu ujian berlangsung.</li>
                            <li>Setiap pelanggaran tercatat secara otomatis pada panel Pengawas Ruang dan Administrator.</li>
                            <li>Apabila jumlah pelanggaran mencapai batas maksimal (<strong id="paktaBatasCheat">3 Kali</strong>), sistem akan menghentikan dan mengumpulkan lembar jawaban secara otomatis.</li>
                        </ul>
                    </div>

                    <label class="pakta-checkbox-label">
                        <input type="checkbox" id="chkSetujuPakta" required style="width:18px; height:18px; margin-top:2px; accent-color:#1D5DEC;">
                        <span>Saya telah membaca dan menyetujui seluruh ketentuan Pakta Integritas serta bersedia mengerjakan ujian secara jujur dan mandiri.</span>
                    </label>
                </div>

                <div style="padding:14px 22px; background:#F8FAFC; border-top:1px solid #F1F5F9; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalPaktaIntegritas')">Batal</button>
                    <button type="submit" id="btnSubmitPakta" class="cbt-btn primary">
                        Setujui &amp; Mulai Kerjakan Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/murid/dashboard.js') }}"></script>
@endpush