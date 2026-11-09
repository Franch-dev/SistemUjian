/**
 * ==========================================================================
 * ADMIN/MONITOR.JS — OTOMATISASI JAM UJIAN, PERINGATAN OVERRIDE, & HAPUS SESI
 * ==========================================================================
 */

let LIST_SESI_UJIAN = [];
let LIST_LIVE_PESERTA = [];
let SELECTED_DAY_FILTER = 'ALL';

const HARI_INDONESIA = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const HARI_SEKOLAH = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

document.addEventListener('DOMContentLoaded', () => {
    muatFilterMasterMonitor();
    muatSesiKontrol();
    muatLiveMonitor();

    // Auto-Sync setiap 20 detik agar saat masuk Jam Mulai atau lewat Jam Selesai, status otomatis berubah
    setInterval(() => {
        muatSesiKontrol();
        muatLiveMonitor();
    }, 20000);
});

function getNamaHariDariTanggal(dateStr) {
    if (!dateStr) return 'Senin';
    const cleanDate = String(dateStr).substring(0, 10);
    const parts = cleanDate.split('-');
    if (parts.length !== 3) return 'Senin';
    const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    return HARI_INDONESIA[d.getDay()] || 'Senin';
}

function formatTanggalIndoLengkap(dateStr) {
    if (!dateStr) return '-';
    const cleanDate = String(dateStr).substring(0, 10);
    const parts = cleanDate.split('-');
    if (parts.length !== 3) return cleanDate;
    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const hari = getNamaHariDariTanggal(cleanDate);
    return `${hari}, ${parts[2]} ${bulan[Number(parts[1]) - 1]} ${parts[0]}`;
}

function formatJamMenit(timeStr) {
    if (!timeStr) return '--:--';
    return String(timeStr).substring(0, 5);
}

function formatLabelStatusUjian(raw) {
    const map = {
        'mengerjakan': 'Sedang Mengerjakan',
        'selesai_mandiri': 'Selesai Mandiri',
        'auto_submit_waktu': 'Selesai (Waktu Habis)',
        'auto_submit_pelanggaran': 'Dihentikan (Batas Cheat)',
        'diskualifikasi': 'Diskualifikasi Admin',
        'belum_mulai': 'Belum Mulai'
    };
    return map[raw] || raw;
}

/**
 * Mengecek apakah waktu sekarang berada di dalam rentang Tanggal & Jam Ujian
 */
function cekApakahDalamJamJadwal(sesi) {
    if (!sesi || !sesi.tanggal_ujian) return false;
    const now = new Date();
    const cleanDate = String(sesi.tanggal_ujian).substring(0, 10);
    const [y, m, d] = cleanDate.split('-').map(Number);

    const [jmMulai, mnMulai] = String(sesi.jam_mulai || '00:00').split(':').map(Number);
    const [jmSelesai, mnSelesai] = String(sesi.jam_selesai || '23:59').split(':').map(Number);

    const startDt = new Date(y, m - 1, d, jmMulai, mnMulai, 0);
    const endDt = new Date(y, m - 1, d, jmSelesai, mnSelesai, 59);

    return now >= startDt && now <= endDt;
}

async function muatFilterMasterMonitor() {
    const [resJ, resK, resP] = await Promise.all([
        cbtFetch('/api/admin/jurusan'),
        cbtFetch('/api/admin/kelas'),
        cbtFetch('/api/admin/plotting')
    ]);
    const [jData, kData, pData] = await Promise.all([resJ.json(), resK.json(), resP.json()]);

    const selJur = document.getElementById('filterSesiJurusan');
    selJur.innerHTML = '<option value="">Semua Jurusan</option>';
    (jData.data || []).forEach(j => {
        selJur.innerHTML += `<option value="${j.kode_jurusan}">Jurusan ${j.kode_jurusan}</option>`;
    });

    const selKls = document.getElementById('filterSesiKelas');
    selKls.innerHTML = '<option value="">Semua Kelas</option>';
    (kData.data || []).forEach(k => {
        selKls.innerHTML += `<option value="${k.id_kelas}">${k.nama_kelas}</option>`;
    });

    const selPlot = document.getElementById('sPlotting');
    if (selPlot) {
        selPlot.innerHTML = '';
        (pData.data || []).forEach(p => {
            selPlot.innerHTML += `<option value="${p.id_plotting}" data-mapel="${p.mapel?.nama_mapel || ''}" data-kelas="${p.kelas?.nama_kelas || ''}">${p.kelas?.nama_kelas} — ${p.mapel?.nama_mapel} (${p.guru?.nama_lengkap})</option>`;
        });
    }
}

function isiNamaSesiOtomatis() {
    const sel = document.getElementById('sPlotting');
    if (!sel || !sel.selectedOptions.length) return;
    const opt = sel.selectedOptions[0];
    const mapel = opt.getAttribute('data-mapel') || '';
    const kelas = opt.getAttribute('data-kelas') || '';
    if (!document.getElementById('editSesiId').value) {
        document.getElementById('sNama').value = `Ujian CBT — ${mapel} ${kelas}`;
    }
}

async function muatSesiKontrol() {
    const res = await cbtFetch('/api/admin/sesi-ujian');
    const json = await res.json();
    LIST_SESI_UJIAN = json.data || [];
    renderTabHariAdmin();
    renderSliderSesi();
}

function renderTabHariAdmin() {
    const container = document.getElementById('adminDayTabsContainer');
    const deleteBox = document.getElementById('dayDeleteActionBox');
    if (!container) return;

    const counts = { ALL: LIST_SESI_UJIAN.length };
    HARI_SEKOLAH.forEach(h => counts[h] = 0);

    LIST_SESI_UJIAN.forEach(s => {
        const h = getNamaHariDariTanggal(s.tanggal_ujian);
        if (counts[h] !== undefined) counts[h]++;
    });

    let html = `
        <button type="button" class="day-tab-btn ${SELECTED_DAY_FILTER === 'ALL' ? 'active' : ''}" onclick="pilihTabHariAdmin('ALL')">
            <span>Semua Hari</span>
            <span class="day-count-badge">${counts.ALL}</span>
        </button>
    `;

    HARI_SEKOLAH.forEach(hari => {
        html += `
            <button type="button" class="day-tab-btn ${SELECTED_DAY_FILTER === hari ? 'active' : ''}" onclick="pilihTabHariAdmin('${hari}')">
                <span>${hari}</span>
                <span class="day-count-badge">${counts[hari]}</span>
            </button>
        `;
    });

    container.innerHTML = html;

    if (deleteBox) {
        const currentCount = counts[SELECTED_DAY_FILTER] || 0;
        if (currentCount > 0) {
            const labelBtn = SELECTED_DAY_FILTER === 'ALL'
                ? `Kosongkan Semua Jadwal (${currentCount})`
                : `Hapus Jadwal Hari ${SELECTED_DAY_FILTER} (${currentCount})`;
            deleteBox.innerHTML = `
                <button type="button" class="btn-text-action red" onclick="hapusJadwalPerHari('${SELECTED_DAY_FILTER}', ${currentCount})">
                    ${labelBtn}
                </button>
            `;
        } else {
            deleteBox.innerHTML = '';
        }
    }
}

function pilihTabHariAdmin(hari) {
    SELECTED_DAY_FILTER = hari;
    renderTabHariAdmin();
    renderSliderSesi();
}

function renderSliderSesi() {
    const kodeJur = document.getElementById('filterSesiJurusan')?.value || '';
    const idKelas = document.getElementById('filterSesiKelas')?.value || '';
    const keyword = (document.getElementById('searchSesiInput')?.value || '').toLowerCase();

    const filtered = LIST_SESI_UJIAN.filter(s => {
        const hariSesi = getNamaHariDariTanggal(s.tanggal_ujian);
        const matchDay = SELECTED_DAY_FILTER === 'ALL' || hariSesi === SELECTED_DAY_FILTER;
        const namaKelas = s.plotting?.kelas?.nama_kelas || '';
        const kodeJurusanRelasi = s.plotting?.kelas?.jurusan?.kode_jurusan || '';
        const namaMapel = s.plotting?.mapel?.nama_mapel || '';
        const matchJur = !kodeJur || kodeJurusanRelasi === kodeJur || namaKelas.includes(kodeJur);
        const matchKls = !idKelas || String(s.plotting?.id_kelas) === String(idKelas);
        const matchQ = s.nama_sesi_ujian.toLowerCase().includes(keyword) || namaMapel.toLowerCase().includes(keyword);
        return matchDay && matchJur && matchKls && matchQ;
    });

    const track = document.getElementById('sliderSesiContainer');
    track.innerHTML = '';

    if (filtered.length === 0) {
        const infoHari = SELECTED_DAY_FILTER === 'ALL' ? '' : ` pada Hari ${SELECTED_DAY_FILTER}`;
        track.innerHTML = `<div style="grid-column: 1 / -1; padding:28px; text-align:center; color:#64748B; font-size:13px; background:#F8FAFC; border-radius:12px; border:1px dashed #CBD5E1;">Tidak ada jadwal sesi ujian${infoHari}. Klik tombol <strong>+ Buat Jadwal Sesi</strong> di kanan atas untuk menambahkan.</div>`;
        return;
    }

    filtered.forEach(s => {
        const buka = s.status_sesi === 'terbuka';
        const tampil = s.visibilitas_jadwal === 'tampil';
        const publik = Boolean(s.publikasi_nilai);
        const kelas = s.plotting?.kelas?.nama_kelas || '-';
        const mapel = s.plotting?.mapel?.nama_mapel || '-';
        const kodeMapel = s.plotting?.mapel?.kode_mapel || '';
        const guru = s.plotting?.guru?.nama_lengkap || '-';
        const jmlSoal = s.plotting?.bank_soal_count ?? 0;
        const tglLengkap = formatTanggalIndoLengkap(s.tanggal_ujian);
        const jamStr = `${formatJamMenit(s.jam_mulai)} – ${formatJamMenit(s.jam_selesai)} WIB`;
        const isOverride = s.catatan_berita_acara === 'FORCE_OPEN_ADMIN' && buka;

        track.innerHTML += `
            <div class="session-clean-card ${buka ? 'open-card' : ''}">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span class="status-pill blue">${kelas}</span>
                        <div style="display:flex; gap:6px;">
                            ${isOverride ? `<span class="status-pill red">Dibuka di Luar Jam</span>` : ''}
                            <span class="status-pill ${jmlSoal > 0 ? 'green' : 'amber'}">${jmlSoal} Soal</span>
                        </div>
                    </div>

                    <h4 style="font-size:15.5px; font-weight:800; color:#0F172A;">${mapel}</h4>
                    <span class="font-mono" style="font-size:11px; color:#64748B; display:block; margin-bottom:10px;">${kodeMapel} • ${guru}</span>

                    <div class="session-meta-clean">
                        <div><strong>Jadwal:</strong> ${tglLengkap}</div>
                        <div><strong>Waktu:</strong> <span class="font-mono">${jamStr} (${s.durasi_menit} Menit)</span></div>
                    </div>
                </div>

                <div class="clean-switches-box">
                    <div class="clean-switch-item">
                        <span>Buka Sesi Ujian (${buka ? 'TERBUKA' : 'TERTUTUP'})</span>
                        <label class="cbt-switch">
                            <input type="checkbox" ${buka ? 'checked' : ''} onchange="ubahToggleSesi(${s.id_sesi}, { status_sesi: this.checked ? 'terbuka' : 'tertutup' }, 'mengubah status sesi')">
                            <span class="cbt-slider-toggle green"></span>
                        </label>
                    </div>

                    <div class="clean-switch-item">
                        <span>Tampilkan di Jadwal Murid</span>
                        <label class="cbt-switch">
                            <input type="checkbox" ${tampil ? 'checked' : ''} onchange="ubahToggleSesi(${s.id_sesi}, { visibilitas_jadwal: this.checked ? 'tampil' : 'tutup' }, 'mengubah visibilitas jadwal')">
                            <span class="cbt-slider-toggle"></span>
                        </label>
                    </div>

                    <div class="clean-switch-item">
                        <span>Publikasikan Hasil Nilai</span>
                        <label class="cbt-switch">
                            <input type="checkbox" ${publik ? 'checked' : ''} onchange="ubahToggleSesi(${s.id_sesi}, { publikasi_nilai: this.checked }, 'mengubah publikasi nilai')">
                            <span class="cbt-slider-toggle"></span>
                        </label>
                    </div>
                </div>

                <div class="card-footer-clean">
                    <div>
                        <button type="button" class="btn-text-action blue" onclick="bukaModalEditSesi(${s.id_sesi})">Edit Jadwal</button>
                        <button type="button" class="btn-text-action slate" onclick="cbtDownloadFile('/api/admin/cetak/daftar-hadir/${s.id_sesi}', 'berita_acara_sesi_${s.id_sesi}.pdf')">Berita Acara PDF</button>
                    </div>
                    <button type="button" class="btn-text-action red" onclick="hapusSesiSatuan(${s.id_sesi}, '${mapel.replace(/'/g, "\\'")}', '${kelas}')">
                        Hapus
                    </button>
                </div>
            </div>
        `;
    });
}

function updatePreviewHariModal() {
    const val = document.getElementById('sTanggal').value;
    const el = document.getElementById('previewHariTeks');
    if (!val) {
        el.textContent = 'Hari Pelaksanaan: -';
        return;
    }
    el.textContent = `Masuk ke jadwal: ${formatTanggalIndoLengkap(val)}`;
}

function bukaModalTambahSesi() {
    document.getElementById('editSesiId').value = '';
    document.getElementById('groupSelectPlotting').style.display = 'block';
    document.getElementById('sTanggal').value = new Date().toISOString().substring(0, 10);
    document.getElementById('sJamMulai').value = '07:30';
    document.getElementById('sJamSelesai').value = '09:00';
    document.getElementById('sDurasi').value = 90;
    document.getElementById('sBatasCheat').value = 3;
    document.getElementById('titleModalSesi').textContent = 'Buat Jadwal Sesi Ujian Baru';
    isiNamaSesiOtomatis();
    updatePreviewHariModal();
    openModal('modalBuatSesi');
}

function bukaModalEditSesi(idSesi) {
    const s = LIST_SESI_UJIAN.find(x => x.id_sesi === idSesi);
    if (!s) return;

    document.getElementById('editSesiId').value = s.id_sesi;
    document.getElementById('groupSelectPlotting').style.display = 'none';
    document.getElementById('sNama').value = s.nama_sesi_ujian;
    document.getElementById('sTanggal').value = String(s.tanggal_ujian).substring(0, 10);
    document.getElementById('sJamMulai').value = formatJamMenit(s.jam_mulai);
    document.getElementById('sJamSelesai').value = formatJamMenit(s.jam_selesai);
    document.getElementById('sDurasi').value = s.durasi_menit;
    document.getElementById('sBatasCheat').value = s.batas_toleransi_anticheat;
    document.getElementById('titleModalSesi').textContent = `Edit Jadwal — ${s.plotting?.mapel?.nama_mapel || ''}`;
    updatePreviewHariModal();
    openModal('modalBuatSesi');
}

async function simpanSesiUjian(e) {
    e.preventDefault();
    const idEdit = document.getElementById('editSesiId').value;
    const tgl = document.getElementById('sTanggal').value;
    const infoHari = formatTanggalIndoLengkap(tgl);

    const yakin = await cbtConfirm(
        `Simpan jadwal pelaksanaan ujian pada <strong>${infoHari}</strong> pukul <strong>${document.getElementById('sJamMulai').value} – ${document.getElementById('sJamSelesai').value} WIB</strong>?`,
        'Konfirmasi Jadwal Ujian'
    );
    if (!yakin) return;

    const payload = {
        id_plotting: document.getElementById('sPlotting').value,
        nama_sesi_ujian: document.getElementById('sNama').value,
        tanggal_ujian: tgl,
        jam_mulai: document.getElementById('sJamMulai').value,
        jam_selesai: document.getElementById('sJamSelesai').value,
        durasi_menit: document.getElementById('sDurasi').value,
        batas_toleransi_anticheat: document.getElementById('sBatasCheat').value
    };

    const url = idEdit ? `/api/admin/sesi-ujian/${idEdit}` : '/api/admin/sesi-ujian';
    const method = idEdit ? 'PUT' : 'POST';

    const res = await cbtFetch(url, { method, body: JSON.stringify(payload) });
    const data = await res.json();
    closeModal('modalBuatSesi');
    await cbtAlert(data.message, res.ok ? 'Jadwal Disimpan' : 'Gagal', res.ok ? 'success' : 'error');
    if (res.ok) await muatSesiKontrol();
}

/**
 * Saklar Toggle Sesi dengan Peringatan Khusus Apabila Dibuka di Luar Jam Jadwal Ujian
 */
async function ubahToggleSesi(id, payload, labelAksi) {
    const sesi = LIST_SESI_UJIAN.find(x => Number(x.id_sesi) === Number(id));
    let pesanKonfirmasi = `Apakah Anda yakin ingin ${labelAksi} pada sesi ujian ini?`;
    let judulKonfirmasi = 'Konfirmasi Saklar Sesi';
    let tipeDialog = 'question';

    if (payload.status_sesi === 'terbuka' && sesi && !cekApakahDalamJamJadwal(sesi)) {
        const tglInfo = formatTanggalIndoLengkap(sesi.tanggal_ujian);
        const jamInfo = `${formatJamMenit(sesi.jam_mulai)} – ${formatJamMenit(sesi.jam_selesai)} WIB`;
        judulKonfirmasi = 'Peringatan Di Luar Jam Ujian';
        tipeDialog = 'danger';
        pesanKonfirmasi =
            `<strong>PERHATIAN: Saat ini berada di luar jadwal ujian!</strong><br><br>` +
            `Jadwal sesi <strong>${sesi.plotting?.mapel?.nama_mapel || ''}</strong> adalah:<br>` +
            `• Hari/Tanggal: <strong>${tglInfo}</strong><br>` +
            `• Jam Ujian: <strong>${jamInfo}</strong><br><br>` +
            `Apakah Anda yakin ingin tetap <strong>Membuka Paksa (Override)</strong> sesi ujian ini di luar jam jadwal?`;
        payload.force_override = true;
    }

    const yakin = await cbtConfirm(pesanKonfirmasi, judulKonfirmasi, tipeDialog);
    if (!yakin) {
        renderSliderSesi();
        return;
    }

    await cbtFetch(`/api/admin/sesi-ujian/${id}/toggle`, {
        method: 'PATCH',
        body: JSON.stringify(payload)
    });
    await muatSesiKontrol();
}

async function aksiMassalSesi(payload, judulAksi) {
    const kodeJur = document.getElementById('filterSesiJurusan')?.value || '';
    const idKelas = document.getElementById('filterSesiKelas')?.value || '';
    const targetLabel = idKelas ? 'Kelas Terpilih' : (kodeJur ? `Jurusan ${kodeJur}` : 'Seluruh Jurusan & Kelas');

    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin <strong>${judulAksi}</strong> secara serentak untuk <strong>${targetLabel}</strong>?`,
        'Konfirmasi Saklar Massal'
    );
    if (!yakin) return;

    const res = await cbtFetch('/api/admin/sesi-ujian/bulk-toggle', {
        method: 'POST',
        body: JSON.stringify({
            kode_jurusan: kodeJur || null,
            id_kelas: idKelas || null,
            ...payload
        })
    });
    const data = await res.json();
    await cbtAlert(data.message, 'Saklar Massal Berhasil', 'success');
    await muatSesiKontrol();
}

async function hapusSesiSatuan(idSesi, mapel, kelas) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghapus jadwal sesi ujian <strong>${mapel} (${kelas})</strong> secara permanen?`,
        'Konfirmasi Hapus Jadwal Ujian',
        'danger'
    );
    if (!yakin) return;

    const res = await cbtFetch(`/api/admin/sesi-ujian/${idSesi}`, { method: 'DELETE' });
    const data = await res.json();

    if (res.ok) {
        LIST_SESI_UJIAN = LIST_SESI_UJIAN.filter(s => Number(s.id_sesi) !== Number(idSesi));
        renderTabHariAdmin();
        renderSliderSesi();
        await cbtAlert(data.message || 'Sesi ujian berhasil dihapus.', 'Jadwal Dihapus', 'success');
        await muatSesiKontrol();
    } else {
        await cbtAlert(data.message || 'Gagal menghapus sesi ujian.', 'Gagal Menghapus', 'error');
    }
}

async function hapusJadwalPerHari(namaHari, jumlahUjian) {
    const labelTarget = namaHari === 'ALL' ? `SELURUH HARI (${jumlahUjian} ujian)` : `HARI ${namaHari.toUpperCase()} (${jumlahUjian} ujian)`;

    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghapus seluruh jadwal ujian pada <strong>${labelTarget}</strong> secara permanen?`,
        'Konfirmasi Hapus Jadwal Hari',
        'danger'
    );
    if (!yakin) return;

    const res = await cbtFetch('/api/admin/sesi-ujian/delete-by-day', {
        method: 'POST',
        body: JSON.stringify({ hari: namaHari })
    });
    const data = await res.json();

    if (res.ok) {
        await cbtAlert(data.message, 'Jadwal Hari Dihapus', 'success');
        await muatSesiKontrol();
    } else {
        await cbtAlert(data.message || 'Gagal menghapus jadwal hari.', 'Gagal', 'error');
    }
}

async function muatLiveMonitor() {
    const res = await cbtFetch('/api/admin/live-monitoring');
    const json = await res.json();
    LIST_LIVE_PESERTA = json.peserta || [];
    renderTabelLivePeserta();
}

function renderTabelLivePeserta() {
    const kodeJur = document.getElementById('filterSesiJurusan')?.value || '';
    const idKelas = document.getElementById('filterSesiKelas')?.value || '';
    const q = (document.getElementById('searchLivePeserta')?.value || '').toLowerCase();
    const statusFilter = document.getElementById('filterLiveStatus')?.value || '';

    const rows = LIST_LIVE_PESERTA.filter(p => {
        const nama = (p.murid?.nama_lengkap || '').toLowerCase();
        const nis = (p.murid?.nis || '').toLowerCase();
        const kelas = p.murid?.kelas?.nama_kelas || '';
        const jur = p.murid?.kelas?.jurusan?.kode_jurusan || '';
        const mapel = (p.sesi_ujian?.plotting?.mapel?.nama_mapel || '').toLowerCase();

        const matchJur = !kodeJur || jur === kodeJur || kelas.includes(kodeJur);
        const matchKls = !idKelas || String(p.murid?.id_kelas) === String(idKelas);
        const matchQ = !q || nama.includes(q) || nis.includes(q) || mapel.includes(q);
        const matchStatus = !statusFilter || p.status_ujian === statusFilter;

        return matchJur && matchKls && matchQ && matchStatus;
    });

    const tbody = document.getElementById('tbodyLivePeserta');
    tbody.innerHTML = '';

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:#64748B;">Belum ada peserta ujian untuk filter ini.</td></tr>`;
        return;
    }

    rows.forEach(p => {
        const m = Math.floor((p.sisa_waktu_detik || 0) / 60);
        const d = (p.sisa_waktu_detik || 0) % 60;
        const nama = p.murid?.nama_lengkap || 'Peserta';
        const kelas = p.murid?.kelas?.nama_kelas || '-';
        const jurusan = p.murid?.kelas?.jurusan?.kode_jurusan || '-';
        const mapel = p.sesi_ujian?.plotting?.mapel?.nama_mapel || '-';
        const kodeMapel = p.sesi_ujian?.plotting?.mapel?.kode_mapel || '';
        const namaSesi = p.sesi_ujian?.nama_sesi_ujian || '-';
        const batasCheat = p.sesi_ujian?.batas_toleransi_anticheat || 3;

        tbody.innerHTML += `
            <tr>
                <td>
                    <strong>${nama}</strong><br>
                    <small class="font-mono">NIS: ${p.murid?.nis || '-'}</small>
                </td>
                <td>
                    <span class="status-pill blue">${kelas}</span><br>
                    <small style="color:#64748B;">Jurusan: ${jurusan}</small>
                </td>
                <td>
                    <strong>${mapel}</strong> <small class="font-mono">(${kodeMapel})</small><br>
                    <small style="color:#64748B;">${namaSesi}</small>
                </td>
                <td class="font-mono"><strong>${p.daftar_jawaban_count || 0} Soal</strong></td>
                <td class="font-mono"><strong>${String(m).padStart(2, '0')}:${String(d).padStart(2, '0')}</strong></td>
                <td>
                    <span class="status-pill ${p.jumlah_pelanggaran > 0 ? 'red' : 'green'}">
                        ${p.jumlah_pelanggaran} / ${batasCheat}
                    </span>
                </td>
                <td><span class="status-pill blue">${formatLabelStatusUjian(p.status_ujian)}</span></td>
                <td>
                    ${p.status_ujian === 'mengerjakan'
                        ? `<button class="cbt-btn danger" onclick="eksekusiForceClose(${p.id_ujian_murid}, '${nama.replace(/'/g, "\\'")}')">Force Close</button>`
                        : `<span class="font-mono"><strong>Nilai: ${p.hasil_nilai?.nilai_akhir ?? '-'}</strong> (${p.hasil_nilai?.status_kelulusan ?? '-'})</span>`
                    }
                </td>
            </tr>
        `;
    });
}

async function eksekusiForceClose(idUjianMurid, nama) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghentikan paksa (<strong>Force Close / Diskualifikasi</strong>) sesi ujian milik <strong>${nama}</strong>?`,
        'Konfirmasi Diskualifikasi Peserta',
        'danger'
    );
    if (!yakin) return;

    const res = await cbtFetch(`/api/admin/live-monitoring/${idUjianMurid}/force-close`, { method: 'POST' });
    const data = await res.json();
    await cbtAlert(data.message, 'Sesi Dihentikan Paksa', 'success');
    muatLiveMonitor();
}