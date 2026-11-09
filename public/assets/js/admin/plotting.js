let LIST_PLOTTING = [];

document.addEventListener('DOMContentLoaded', muatHalamanPlotting);

async function muatHalamanPlotting() {
    const [resG, resM, resK, resP] = await Promise.all([
        cbtFetch('/api/admin/guru'),
        cbtFetch('/api/admin/mapel'),
        cbtFetch('/api/admin/kelas'),
        cbtFetch('/api/admin/plotting')
    ]);
    const [g, m, k, p] = await Promise.all([resG.json(), resM.json(), resK.json(), resP.json()]);

    LIST_PLOTTING = p.data || [];

    const selG = document.getElementById('pGuru');
    const selM = document.getElementById('pMapel');
    const selK = document.getElementById('pKelas');
    const filK = document.getElementById('filterPlottingKelas');

    selG.innerHTML = ''; selM.innerHTML = ''; selK.innerHTML = '';
    filK.innerHTML = '<option value="">Semua Kelas</option>';

    (g.data || []).forEach(x => selG.innerHTML += `<option value="${x.id_guru}">${x.nama_lengkap} (${x.kode_guru})</option>`);
    (m.data || []).forEach(x => selM.innerHTML += `<option value="${x.id_mapel}">${x.nama_mapel} (KKM ${x.nilai_kkm})</option>`);
    (k.data || []).forEach(x => {
        selK.innerHTML += `<option value="${x.id_kelas}">${x.nama_kelas}</option>`;
        filK.innerHTML += `<option value="${x.id_kelas}">${x.nama_kelas}</option>`;
    });

    renderPlotting();
}

function renderPlotting() {
    const q = (document.getElementById('searchPlotting')?.value || '').toLowerCase();
    const idKelas = document.getElementById('filterPlottingKelas')?.value || '';
    const sort = document.getElementById('sortPlotting')?.value || 'guru_asc';

    let rows = LIST_PLOTTING.filter(item => {
        const matchQ = (item.guru?.nama_lengkap || '').toLowerCase().includes(q) || (item.mapel?.nama_mapel || '').toLowerCase().includes(q);
        const matchK = !idKelas || String(item.id_kelas) === String(idKelas);
        return matchQ && matchK;
    });

    rows.sort((a, b) => {
        if (sort === 'mapel_asc') return (a.mapel?.nama_mapel || '').localeCompare(b.mapel?.nama_mapel || '');
        if (sort === 'soal_desc') return (b.bank_soal_count || 0) - (a.bank_soal_count || 0);
        return (a.guru?.nama_lengkap || '').localeCompare(b.guru?.nama_lengkap || '');
    });

    const tbody = document.getElementById('tbodyPlotting');
    tbody.innerHTML = '';

    rows.forEach(item => {
        tbody.innerHTML += `
            <tr>
                <td><strong>${item.guru?.nama_lengkap || '-'}</strong></td>
                <td>${item.mapel?.nama_mapel || '-'} <small style="color:#64748B;">(KKM ${item.mapel?.nilai_kkm || 75})</small></td>
                <td><span class="status-pill blue">${item.kelas?.nama_kelas || '-'}</span></td>
                <td><strong>${item.bank_soal_count || 0} Butir Soal</strong></td>
                <td>
                    <button class="cbt-btn danger" onclick="hapusPlotting(${item.id_plotting}, '${(item.mapel?.nama_mapel || '').replace(/'/g, "\\'")}', '${item.kelas?.nama_kelas || ''}')">Hapus</button>
                </td>
            </tr>
        `;
    });
}

async function simpanPlotting(e) {
    e.preventDefault();
    const yakin = await cbtConfirm(
        'Apakah Anda yakin ingin menetapkan Plotting Guru Pengampu untuk mata pelajaran dan kelas ini?',
        'Konfirmasi Plotting Guru'
    );
    if (!yakin) return;

    const res = await cbtFetch('/api/admin/plotting', {
        method: 'POST',
        body: JSON.stringify({
            id_guru: document.getElementById('pGuru').value,
            id_mapel: document.getElementById('pMapel').value,
            id_kelas: document.getElementById('pKelas').value
        })
    });
    const data = await res.json();
    closeModal('modalPlotting');
    await cbtAlert(data.message, res.ok ? 'Plotting Berhasil' : 'Konflik Plotting', res.ok ? 'success' : 'error');
    if (res.ok) muatHalamanPlotting();
}

async function hapusPlotting(id, mapel, kelas) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghapus plotting mata pelajaran <strong>${mapel}</strong> di kelas <strong>${kelas}</strong>?`,
        'Konfirmasi Hapus Plotting',
        'danger'
    );
    if (!yakin) return;

    await cbtFetch(`/api/admin/plotting/${id}`, { method: 'DELETE' });
    await cbtAlert('Data plotting berhasil dihapus.');
    muatHalamanPlotting();
}