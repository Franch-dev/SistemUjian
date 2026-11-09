let LIST_REKAP_PESERTA = [];
let CURRENT_KKM = 75;

document.addEventListener('DOMContentLoaded', initHalamanRekapGuru);

async function initHalamanRekapGuru() {
    const res = await cbtFetch('/api/guru/plotting-ampuan');
    const json = await res.json();
    const sel = document.getElementById('selectPlottingRekap');
    sel.innerHTML = '';

    const urlParams = new URLSearchParams(window.location.search);
    const paramPlotting = urlParams.get('id_plotting');

    (json.data || []).forEach(p => {
        const selected = paramPlotting && String(p.id_plotting) === String(paramPlotting) ? 'selected' : '';
        sel.innerHTML += `<option value="${p.id_plotting}" ${selected}>${p.kelas?.nama_kelas} — ${p.mapel?.nama_mapel} (KKM ${p.mapel?.nilai_kkm || 75})</option>`;
    });

    muatRekapDanLog();
}

async function muatRekapDanLog() {
    const idPlotting = document.getElementById('selectPlottingRekap').value;
    if (!idPlotting) return;

    const res = await cbtFetch(`/api/guru/plotting/${idPlotting}/rekap-nilai`);
    const json = await res.json();

    CURRENT_KKM = json.nilai_kkm || 75;
    LIST_REKAP_PESERTA = json.data || [];

    renderTabelRekap();
    renderTabelLogGuru();
}

function renderTabelRekap() {
    const q = (document.getElementById('searchRekapMurid')?.value || '').toLowerCase();
    const filterStatus = document.getElementById('filterKelulusan')?.value || '';

    const rows = LIST_REKAP_PESERTA.filter(item => {
        const nama = (item.murid?.nama_lengkap || '').toLowerCase();
        const nis = (item.murid?.nis || '').toLowerCase();
        const statusKelulusan = item.hasil_nilai?.status_kelulusan || '';

        const matchQ = !q || nama.includes(q) || nis.includes(q);
        const matchSt = !filterStatus || statusKelulusan === filterStatus;
        return matchQ && matchSt;
    });

    const tbody = document.getElementById('tbodyRekapNilai');
    tbody.innerHTML = '';

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#64748B;">Belum ada data nilai ujian pada kelas ampuan ini.</td></tr>`;
        return;
    }

    rows.forEach(item => {
        const nilai = item.hasil_nilai?.nilai_akhir ?? '-';
        const benar = item.hasil_nilai?.jumlah_benar ?? 0;
        const statusLulus = item.hasil_nilai?.status_kelulusan || 'Belum Selesai';
        const isLulus = statusLulus === 'Lulus';

        tbody.innerHTML += `
            <tr>
                <td class="font-mono"><strong>${item.murid?.nis || '-'}</strong></td>
                <td><strong>${item.murid?.nama_lengkap || '-'}</strong></td>
                <td><span class="status-pill blue">KKM ${CURRENT_KKM}</span></td>
                <td class="font-mono">${benar} Soal</td>
                <td class="font-mono"><strong style="font-size:14px;">${nilai}</strong></td>
                <td>
                    <span class="status-pill ${isLulus ? 'green' : 'red'}">
                        ${statusLulus.toUpperCase()}
                    </span>
                </td>
                <td><span class="status-pill amber">${item.status_ujian}</span></td>
            </tr>
        `;
    });
}

function renderTabelLogGuru() {
    const tbody = document.getElementById('tbodyLogPelanggaranGuru');
    tbody.innerHTML = '';

    const allLogs = [];
    LIST_REKAP_PESERTA.forEach(u => {
        (u.log_pelanggaran || []).forEach(log => {
            allLogs.push({
                waktu: log.waktu_pelanggaran,
                nis: u.murid?.nis || '-',
                nama: u.murid?.nama_lengkap || '-',
                jenis: log.jenis_pelanggaran,
                total: u.jumlah_pelanggaran
            });
        });
    });

    if (allLogs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#64748B;">Tidak ada insiden pelanggaran anti-cheat pada kelas ini.</td></tr>`;
        return;
    }

    allLogs.forEach(l => {
        tbody.innerHTML += `
            <tr>
                <td class="font-mono">${l.waktu || '-'}</td>
                <td class="font-mono"><strong>${l.nis}</strong></td>
                <td><strong>${l.nama}</strong></td>
                <td style="color:#E11D48; font-weight:600;">${l.jenis}</td>
                <td><span class="status-pill red">${l.total}x Pelanggaran</span></td>
            </tr>
        `;
    });
}

function unduhRekapExcelGuru() {
    const idPlotting = document.getElementById('selectPlottingRekap').value;
    cbtDownloadFile(`/api/guru/rekap-nilai/export?id_plotting=${idPlotting}&format=xlsx`, `rekap_nilai_plotting_${idPlotting}.xlsx`);
}

function unduhLogCheatExcelGuru() {
    const idPlotting = document.getElementById('selectPlottingRekap').value;
    cbtDownloadFile(`/api/guru/log-pelanggaran/export?id_plotting=${idPlotting}&format=xlsx`, `log_pelanggaran_plotting_${idPlotting}.xlsx`);
}