let DATA_PLOTTING_GURU = [];

document.addEventListener('DOMContentLoaded', initDaftarHadirGuru);

async function initDaftarHadirGuru() {
    const res = await cbtFetch('/api/guru/plotting-ampuan');
    const json = await res.json();
    DATA_PLOTTING_GURU = json.data || [];

    const selPlot = document.getElementById('selectPlottingHadir');
    selPlot.innerHTML = '';

    DATA_PLOTTING_GURU.forEach(p => {
        selPlot.innerHTML += `<option value="${p.id_plotting}">${p.kelas?.nama_kelas} — ${p.mapel?.nama_mapel}</option>`;
    });

    muatDaftarHadirKelas();
}

async function muatDaftarHadirKelas() {
    const idPlotting = document.getElementById('selectPlottingHadir').value;
    if (!idPlotting) return;

    const plotObj = DATA_PLOTTING_GURU.find(x => String(x.id_plotting) === String(idPlotting));
    const selSesi = document.getElementById('selectSesiHadir');
    selSesi.innerHTML = '';

    (plotObj?.sesi_ujian || []).forEach(s => {
        selSesi.innerHTML += `<option value="${s.id_sesi}">${s.nama_sesi_ujian} (${s.tanggal_ujian})</option>`;
    });

    const res = await cbtFetch(`/api/guru/plotting/${idPlotting}/rekap-nilai`);
    const json = await res.json();

    const semuaMurid = json.semua_murid_kelas || [];
    const ujianList = json.data || [];

    const tbody = document.getElementById('tbodyDaftarHadir');
    tbody.innerHTML = '';

    if (semuaMurid.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#64748B;">Belum ada siswa terdaftar di kelas ini.</td></tr>`;
        return;
    }

    semuaMurid.forEach((m, idx) => {
        const ujian = ujianList.find(u => u.id_murid === m.id_murid);
        const hadir = ujian && ujian.status_ujian !== 'belum_mulai';

        tbody.innerHTML += `
            <tr>
                <td class="font-mono">${idx + 1}</td>
                <td class="font-mono"><strong>${m.nis}</strong></td>
                <td><strong>${m.nama_lengkap}</strong></td>
                <td>
                    <span class="status-pill ${hadir ? 'green' : 'amber'}">
                        ${hadir ? `Hadir (${ujian.status_ujian})` : 'Belum Hadir / Belum Mulai'}
                    </span>
                </td>
                <td><span class="status-pill ${ujian && ujian.jumlah_pelanggaran > 0 ? 'red' : 'blue'}">${ujian ? ujian.jumlah_pelanggaran : 0}x</span></td>
                <td class="font-mono">${ujian?.waktu_mulai || '-'}</td>
            </tr>
        `;
    });
}

async function cetakPdfDaftarHadirGuru() {
    const idSesi = document.getElementById('selectSesiHadir').value;
    if (!idSesi) {
        await cbtAlert('Belum ada sesi ujian yang dijadwalkan oleh Admin untuk kelas ini.', 'Sesi Belum Ada', 'error');
        return;
    }
    cbtDownloadFile(`/api/guru/cetak/daftar-hadir/${idSesi}`, `daftar_hadir_berita_acara_sesi_${idSesi}.pdf`);
}