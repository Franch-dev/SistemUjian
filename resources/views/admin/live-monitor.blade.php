@extends('layouts.admin')

@section('title', 'Kontrol Jadwal & Live Monitor')
@section('page_heading', 'Kontrol Jadwal & Live Monitoring')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin/pages.css') }}">
<style>
    /* Bar Tab Hari yang Bersih */
    .day-tabs-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding-bottom: 14px;
        margin-bottom: 16px;
        border-bottom: 1px solid #F1F5F9;
    }
    .day-tabs-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
    }
    .day-tab-btn {
        padding: 8px 14px;
        border-radius: 9px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #475569;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.16s ease;
    }
    .day-tab-btn:hover {
        background-color: #F8FAFC;
        border-color: #CBD5E1;
        color: #0F172A;
    }
    .day-tab-btn.active {
        background-color: #1D5DEC;
        border-color: #1D5DEC;
        color: #FFFFFF;
        font-weight: 700;
    }
    .day-count-badge {
        background-color: #F1F5F9;
        color: #475569;
        padding: 1px 7px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }
    .day-tab-btn.active .day-count-badge {
        background-color: rgba(255, 255, 255, 0.22);
        color: #FFFFFF;
    }

    /* Grid / Slider Kartu Sesi Ujian yang Clean */
    .session-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
    }
    .session-clean-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .session-clean-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
    }
    .session-clean-card.open-card {
        border-top: 4px solid #10B981;
    }
    .session-meta-clean {
        font-size: 12px;
        color: #475569;
        line-height: 1.6;
        padding: 10px 12px;
        background-color: #F8FAFC;
        border-radius: 10px;
        border: 1px solid #F1F5F9;
    }
    .clean-switches-box {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-top: 4px;
    }
    .clean-switch-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
    }
    .cbt-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 20px;
    }
    .cbt-switch input { opacity: 0; width: 0; height: 0; }
    .cbt-slider-toggle {
        position: absolute; cursor: pointer; inset: 0;
        background-color: #CBD5E1; transition: .2s; border-radius: 20px;
    }
    .cbt-slider-toggle:before {
        position: absolute; content: ""; height: 14px; width: 14px;
        left: 3px; bottom: 3px; background-color: white;
        transition: .2s; border-radius: 50%;
    }
    input:checked + .cbt-slider-toggle { background-color: #1D5DEC; }
    input:checked + .cbt-slider-toggle.green { background-color: #10B981; }
    input:checked + .cbt-slider-toggle:before { transform: translateX(18px); }

    .card-footer-clean {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #F1F5F9;
        gap: 8px;
    }
    .btn-text-action {
        background: none;
        border: none;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background-color 0.15s;
    }
    .btn-text-action.blue { color: #1D5DEC; }
    .btn-text-action.blue:hover { background-color: #EEF4FF; }
    .btn-text-action.slate { color: #64748B; }
    .btn-text-action.slate:hover { background-color: #F1F5F9; color: #0F172A; }
    .btn-text-action.red { color: #E11D48; }
    .btn-text-action.red:hover { background-color: #FFE4E6; }
</style>
@endpush

@section('content')
    <!-- =================================================================
         BAGIAN 1: JADWAL SESI UJIAN PER HARI (CLEAN DESIGN)
         ================================================================= -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Jadwal Pelaksanaan Sesi Ujian</h3>
                <p>Pilih tab hari untuk mengelola jadwal, mengatur jam ujian, atau menghapus jadwal per hari</p>
            </div>
            <div class="btn-group-row">
                <button type="button" class="cbt-btn primary" onclick="bukaModalTambahSesi()">+ Buat Jadwal Sesi</button>
            </div>
        </div>

        <!-- Bar Tab Hari + Tombol Hapus Jadwal Hari Terpilih -->
        <div class="day-tabs-wrapper">
            <div id="adminDayTabsContainer" class="day-tabs-bar"></div>
            <div id="dayDeleteActionBox"></div>
        </div>

        <!-- Bar Filter Ringkas -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <select id="filterSesiJurusan" class="cbt-input-sm" onchange="renderSliderSesi(); muatLiveMonitor();">
                    <option value="">Semua Jurusan</option>
                </select>
                <select id="filterSesiKelas" class="cbt-input-sm" onchange="renderSliderSesi(); muatLiveMonitor();">
                    <option value="">Semua Kelas</option>
                </select>
                <input type="text" id="searchSesiInput" class="cbt-input-sm search-input-wrap" placeholder="Cari mata pelajaran..." oninput="renderSliderSesi()">
            </div>
            <div class="btn-group-row">
                <button type="button" class="cbt-btn outline" onclick="aksiMassalSesi({ status_sesi: 'terbuka', visibilitas_jadwal: 'tampil' }, 'MEMBUKA SEMUA SESI')">
                    Buka Semua Sesi
                </button>
                <button type="button" class="cbt-btn outline" onclick="aksiMassalSesi({ status_sesi: 'tertutup' }, 'MENUTUP SEMUA SESI')">
                    Tutup Semua
                </button>
            </div>
        </div>

        <!-- Grid Kartu Sesi Ujian -->
        <div id="sliderSesiContainer" class="session-cards-grid"></div>
    </div>

    <!-- =================================================================
         BAGIAN 2: TABEL LIVE MONITORING PESERTA UJIAN
         ================================================================= -->
    <div class="page-card">
        <div class="page-card-header">
            <div>
                <h3>Live Monitoring Peserta Ujian</h3>
                <p>Pantau peserta yang sedang mengerjakan ujian beserta sisa waktu dan catatan pelanggaran</p>
            </div>
            <div class="btn-group-row">
                <button type="button" class="cbt-btn outline" onclick="cbtDownloadFile('/api/admin/rekap-nilai/export?format=xlsx', 'rekap_nilai_global.xlsx')">Unduh Rekap Nilai</button>
                <button type="button" class="cbt-btn outline" onclick="cbtDownloadFile('/api/admin/log-pelanggaran/export?format=xlsx', 'log_anticheat.xlsx')">Unduh Log Pelanggaran</button>
                <button type="button" class="cbt-btn outline" onclick="muatLiveMonitor()">Muat Ulang</button>
            </div>
        </div>

        <div class="table-toolbar">
            <div class="toolbar-left">
                <input type="text" id="searchLivePeserta" class="cbt-input-sm search-input-wrap" placeholder="Cari nama murid, NIS, atau mata pelajaran..." oninput="renderTabelLivePeserta()">
                <select id="filterLiveStatus" class="cbt-input-sm" onchange="renderTabelLivePeserta()">
                    <option value="">Semua Status Pengerjaan</option>
                    <option value="mengerjakan">Sedang Mengerjakan</option>
                    <option value="selesai_mandiri">Selesai Mandiri</option>
                    <option value="auto_submit_waktu">Selesai (Waktu Habis)</option>
                    <option value="auto_submit_pelanggaran">Dihentikan (Batas Pelanggaran)</option>
                    <option value="diskualifikasi">Diskualifikasi Admin</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="cbt-data-table">
                <thead>
                    <tr>
                        <th>Nama Murid &amp; NIS</th>
                        <th>Jurusan &amp; Kelas</th>
                        <th>Mata Pelajaran &amp; Sesi</th>
                        <th>Jawaban Masuk</th>
                        <th>Sisa Waktu</th>
                        <th>Pelanggaran</th>
                        <th>Status Ujian</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody id="tbodyLivePeserta"></tbody>
            </table>
        </div>
    </div>

    <!-- MODAL POP-UP BUAT / EDIT JADWAL HARI & JAM SESI UJIAN -->
    <div id="modalBuatSesi" class="cbt-modal-backdrop">
        <div class="cbt-modal-box">
            <div class="cbt-modal-header">
                <h4 id="titleModalSesi">Atur Jadwal Hari &amp; Jam Sesi Ujian</h4>
                <button type="button" class="cbt-modal-close" onclick="closeModal('modalBuatSesi')">✕</button>
            </div>
            <form onsubmit="simpanSesiUjian(event)">
                <input type="hidden" id="editSesiId">
                <div class="cbt-modal-body">
                    <div class="form-field-group" id="groupSelectPlotting">
                        <label>Pilih Plotting Mata Pelajaran &amp; Kelas</label>
                        <select id="sPlotting" class="cbt-input-sm" onchange="isiNamaSesiOtomatis()" required></select>
                    </div>
                    <div class="form-field-group">
                        <label>Nama Sesi Ujian</label>
                        <input type="text" id="sNama" class="cbt-input-sm" placeholder="Contoh: UAS Ganjil — Pengembangan Website" required>
                    </div>
                    <div class="form-field-group">
                        <label>Tanggal Pelaksanaan (Menentukan Hari Ujian)</label>
                        <input type="date" id="sTanggal" class="cbt-input-sm" onchange="updatePreviewHariModal()" required>
                        <small id="previewHariTeks" style="color:#1D5DEC; font-weight:700; font-size:11.5px; margin-top:4px; display:block;">
                            Hari Pelaksanaan: -
                        </small>
                    </div>
                    <div class="password-gen-row">
                        <div class="form-field-group" style="flex:1;">
                            <label>Jam Mulai (WIB)</label>
                            <input type="time" id="sJamMulai" class="cbt-input-sm" value="07:30" required>
                        </div>
                        <div class="form-field-group" style="flex:1;">
                            <label>Jam Selesai (WIB)</label>
                            <input type="time" id="sJamSelesai" class="cbt-input-sm" value="09:00" required>
                        </div>
                    </div>
                    <div class="password-gen-row">
                        <div class="form-field-group" style="flex:1;">
                            <label>Durasi Ujian (Menit)</label>
                            <input type="number" id="sDurasi" class="cbt-input-sm" value="90" min="10" max="300" required>
                        </div>
                        <div class="form-field-group" style="flex:1;">
                            <label>Batas Pelanggaran Anti-Cheat</label>
                            <input type="number" id="sBatasCheat" class="cbt-input-sm" value="3" min="1" max="10" required>
                        </div>
                    </div>
                </div>
                <div class="cbt-modal-footer">
                    <button type="button" class="cbt-btn outline" onclick="closeModal('modalBuatSesi')">Batal</button>
                    <button type="submit" class="cbt-btn primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/admin/monitor.js') }}?v={{ time() }}"></script>
@endpush