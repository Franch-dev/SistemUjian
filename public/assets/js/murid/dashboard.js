/**
 * ==========================================================================
 * MURID/DASHBOARD.JS — JADWAL UJIAN PER HARI, OTOMATISASI JAM & PAKTA INTEGRITAS
 * ==========================================================================
 */

let DAFTAR_JADWAL_MURID = [];
let HARI_AKTIF_MURID = 'ALL';

const NAMA_HARI_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const TAB_HARI_SEKOLAH = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

document.addEventListener('DOMContentLoaded', () => {
    const userRaw = localStorage.getItem('cbt_user');
    if (userRaw) {
        const u = JSON.parse(userRaw);
        document.getElementById('welcomeStudentName').textContent = u.nama_lengkap || 'Peserta';
        document.getElementById('welcomeStudentClass').textContent = `${u.kelas || '-'} — ${u.jurusan || ''}`;
    }

    muatJadwalUjianMurid();

    // Cek otomatis setiap 15 detik agar tepat saat jam mulai tiba, tombol Mulai Ujian langsung aktif otomatis
    setInterval(muatJadwalUjianMurid, 15000);
});

function getHariIndonesia(dateStr) {
    if (!dateStr) return 'Senin';
    const clean = String(dateStr).substring(0, 10);
    const p = clean.split('-');
    if (p.length !== 3) return 'Senin';
    const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
    return NAMA_HARI_ID[d.getDay()] || 'Senin';
}

function formatTanggalBersih(dateStr) {
    if (!dateStr) return '-';
    const clean = String(dateStr).substring(0, 10);
    const p = clean.split('-');
    if (p.length !== 3) return clean;
    const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${getHariIndonesia(clean)}, ${p[2]} ${bulan[Number(p[1]) - 1]} ${p[0]}`;
}

function formatJamBersih(timeStr) {
    if (!timeStr) return '--:--';
    return String(timeStr).substring(0, 5);
}

function formatStatusSelesaiBersih(rawStatus) {
    const labelMap = {
        'selesai_mandiri': 'Selesai Dikerjakan',
        'auto_submit_waktu': 'Selesai (Waktu Habis)',
        'auto_submit_pelanggaran': 'Dihentikan (Batas Pelanggaran)',
        'diskualifikasi': 'Diskualifikasi Pengawas'
    };
    return labelMap[rawStatus] || 'Telah Selesai';
}

async function muatJadwalUjianMurid() {
    const res = await cbtFetch('/api/murid/jadwal-ujian');
    const json = await res.json();
    DAFTAR_JADWAL_MURID = json.data || [];

    // Cek sesi yang sedang berlangsung dan masih terbuka
    const sedangMengerjakan = DAFTAR_JADWAL_MURID.find(
        j => j.status_sesi === 'terbuka' && j.progres_murid && j.progres_murid.status_ujian === 'mengerjakan'
    );

    const resumeBanner = document.getElementById('resumeExamBanner');
    if (sedangMengerjakan) {
        resumeBanner.classList.add('show');
        const mnt = Math.floor((sedangMengerjakan.progres_murid.sisa_waktu_detik || 0) / 60);
        document.getElementById('resumeExamDesc').innerHTML =
            `Mata Pelajaran: <strong>${sedangMengerjakan.nama_mapel}</strong> &nbsp;|&nbsp; Sisa Waktu: <strong>${mnt} Menit</strong> &nbsp;|&nbsp; Catatan Pelanggaran: <strong>${sedangMengerjakan.progres_murid.pelanggaran} dari ${sedangMengerjakan.batas_toleransi_anticheat}</strong>`;
        document.getElementById('btnResumeDirect').onclick = () => bukaModalPakta(sedangMengerjakan.id_sesi);
    } else {
        resumeBanner.classList.remove('show');
    }

    renderTabHariMurid();
    renderKartuJadwalMurid();
}

function renderTabHariMurid() {
    const container = document.getElementById('studentDayTabsContainer');
    if (!container) return;

    const counts = { ALL: DAFTAR_JADWAL_MURID.length };
    TAB_HARI_SEKOLAH.forEach(h => counts[h] = 0);

    DAFTAR_JADWAL_MURID.forEach(item => {
        const h = getHariIndonesia(item.tanggal_ujian);
        if (counts[h] !== undefined) counts[h]++;
    });

    let html = `
        <button type="button" class="student-day-tab ${HARI_AKTIF_MURID === 'ALL' ? 'active' : ''}" onclick="pilihHariJadwalMurid('ALL')">
            <span class="day-name">Semua Hari</span>
            <span class="day-count">${counts.ALL} Mata Pelajaran</span>
        </button>
    `;

    TAB_HARI_SEKOLAH.forEach(hari => {
        html += `
            <button type="button" class="student-day-tab ${HARI_AKTIF_MURID === hari ? 'active' : ''}" onclick="pilihHariJadwalMurid('${hari}')">
                <span class="day-name">Hari ${hari}</span>
                <span class="day-count">${counts[hari]} Ujian Terjadwal</span>
            </button>
        `;
    });

    container.innerHTML = html;
}

function pilihHariJadwalMurid(hari) {
    HARI_AKTIF_MURID = hari;
    renderTabHariMurid();
    renderKartuJadwalMurid();
}

function renderKartuJadwalMurid() {
    const container = document.getElementById('scheduleCardsContainer');
    const summaryEl = document.getElementById('selectedDaySummaryText');
    container.innerHTML = '';

    const filtered = DAFTAR_JADWAL_MURID.filter(item => {
        if (HARI_AKTIF_MURID === 'ALL') return true;
        return getHariIndonesia(item.tanggal_ujian) === HARI_AKTIF_MURID;
    });

    if (HARI_AKTIF_MURID === 'ALL') {
        summaryEl.textContent = `Menampilkan seluruh jadwal ujian (${filtered.length} mata pelajaran)`;
    } else {
        summaryEl.textContent = `Jadwal pelaksanaan ujian pada Hari ${HARI_AKTIF_MURID} (${filtered.length} mata pelajaran)`;
    }

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="page-card" style="grid-column: 1 / -1; text-align:center; padding:38px 24px;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A;">Tidak Ada Jadwal Ujian pada Pilihan Hari Ini</h3>
                <p style="font-size:13px; color:#64748B; margin-top:6px;">
                    Silakan pilih tab hari lainnya di atas atau tunggu Administrator mengaktifkan visibilitas jadwal ujian kelas Anda.
                </p>
            </div>
        `;
        return;
    }

    filtered.forEach(item => {
        const isTerbuka = item.status_sesi === 'terbuka';
        const statusWaktu = item.status_waktu || 'dalam_jadwal';
        const prog = item.progres_murid;
        const sudahSelesai = prog && prog.status_ujian !== 'mengerjakan' && prog.status_ujian !== 'belum_mulai';
        const sedangJalan = prog && prog.status_ujian === 'mengerjakan';

        const hariTgl = formatTanggalBersih(item.tanggal_ujian);
        const jamPelaksanaan = `${formatJamBersih(item.jam_mulai)} – ${formatJamBersih(item.jam_selesai)} WIB`;

        let statusBadgeHtml = '';
        if (sudahSelesai) {
            statusBadgeHtml = `<span class="status-pill green">Selesai Dikerjakan</span>`;
        } else if (isTerbuka) {
            statusBadgeHtml = `<span class="status-pill blue">Sesi Ujian Terbuka</span>`;
        } else if (statusWaktu === 'lewat_jam_selesai') {
            statusBadgeHtml = `<span class="status-pill red">Waktu Ujian Berakhir</span>`;
        } else if (statusWaktu === 'belum_mulai_jam') {
            statusBadgeHtml = `<span class="status-pill amber">Belum Masuk Jam Ujian</span>`;
        } else {
            statusBadgeHtml = `<span class="status-pill slate">Sesi Ditutup</span>`;
        }

        let actionBtnHtml = '';
        if (sudahSelesai) {
            actionBtnHtml = `
                <button type="button" class="cbt-btn outline" style="width:100%; background:#F8FAFC; color:#047857; border-color:#A7F3D0;" disabled>
                    Status: ${formatStatusSelesaiBersih(prog.status_ujian)}
                </button>
            `;
        } else if (!isTerbuka) {
            let alasanTerkunci = 'Menunggu Sesi Dibuka Pengawas';
            if (statusWaktu === 'lewat_jam_selesai') {
                alasanTerkunci = `Jam Ujian Telah Berakhir (${formatJamBersih(item.jam_selesai)} WIB)`;
            } else if (statusWaktu === 'belum_mulai_jam') {
                alasanTerkunci = `Dibuka Otomatis Pukul ${formatJamBersih(item.jam_mulai)} WIB`;
            }
            actionBtnHtml = `
                <button type="button" class="cbt-btn outline" style="width:100%;" disabled>
                    ${alasanTerkunci}
                </button>
            `;
        } else if (sedangJalan) {
            actionBtnHtml = `
                <button type="button" class="cbt-btn warning" style="width:100%;" onclick="bukaModalPakta(${item.id_sesi})">
                    Lanjutkan Pengerjaan Ujian
                </button>
            `;
        } else {
            actionBtnHtml = `
                <button type="button" class="cbt-btn primary" style="width:100%;" onclick="bukaModalPakta(${item.id_sesi})">
                    Mulai Kerjakan Ujian
                </button>
            `;
        }

        container.innerHTML += `
            <div class="exam-card">
                <div>
                    <div class="exam-card-top">
                        ${statusBadgeHtml}
                        <span class="status-pill slate font-mono">Standar KKM: ${item.nilai_kkm}</span>
                    </div>
                    <h3 class="exam-subject-title">${item.nama_mapel}</h3>
                    <p class="exam-session-subtitle">${item.nama_sesi_ujian}</p>
                </div>

                <div class="exam-meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Hari &amp; Tanggal</span>
                        <span class="meta-value">${hariTgl}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Jam Pelaksanaan</span>
                        <span class="meta-value font-mono">${jamPelaksanaan}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Durasi &amp; Toleransi</span>
                        <span class="meta-value">${item.durasi_menit} Menit / Maks. ${item.batas_toleransi_anticheat}x Cheat</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Guru Pengampu</span>
                        <span class="meta-value">${item.guru_pengampu}</span>
                    </div>
                </div>

                <div>${actionBtnHtml}</div>
            </div>
        `;
    });
}

function bukaModalPakta(idSesi) {
    const item = DAFTAR_JADWAL_MURID.find(x => x.id_sesi === idSesi);
    if (!item) return;

    document.getElementById('selectedIdSesi').value = item.id_sesi;
    document.getElementById('paktaNamaMapel').textContent = `${item.nama_mapel} (${item.nama_sesi_ujian})`;
    document.getElementById('paktaDurasiBadge').textContent = `Durasi Pengerjaan: ${item.durasi_menit} Menit`;
    document.getElementById('paktaBatasCheat').textContent = `${item.batas_toleransi_anticheat} Kali`;
    document.getElementById('chkSetujuPakta').checked = false;

    openModal('modalPaktaIntegritas');
}

async function konfirmasiMulaiUjian(e) {
    e.preventDefault();
    const idSesi = document.getElementById('selectedIdSesi').value;

    try {
        if (document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
        }
    } catch (err) {}

    const res = await cbtFetch(`/api/murid/sesi-ujian/${idSesi}/mulai`, {
        method: 'POST',
        body: JSON.stringify({
            pakta_integritas: true,
            fullscreen_lock: true
        })
    });
    const data = await res.json();

    if (res.ok && data.ujian_murid) {
        closeModal('modalPaktaIntegritas');
        window.location.href = `/murid/ujian/${data.ujian_murid.id_ujian_murid}`;
    } else {
        await cbtAlert(data.message || 'Gagal memulai sesi ujian.', 'Tidak Dapat Memulai', 'error');
        muatJadwalUjianMurid();
    }
}