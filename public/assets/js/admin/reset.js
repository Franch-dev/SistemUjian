document.addEventListener('DOMContentLoaded', muatSesiTerkunci);

async function muatSesiTerkunci() {
    const res = await cbtFetch('/api/admin/reset-sessions');
    const json = await res.json();
    const tbody = document.getElementById('tbodyResetSesi');
    tbody.innerHTML = '';

    const list = json.data || [];
    refreshSidebarLockedBadge();

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#64748B;">Tidak ada akun murid yang sedang terkunci saat ini.</td></tr>`;
        return;
    }

    list.forEach(item => {
        const nama = item.murid?.nama_lengkap || 'Murid';
        tbody.innerHTML += `
            <tr>
                <td class="font-mono"><strong>${item.murid?.nis || '-'}</strong></td>
                <td><strong>${nama}</strong></td>
                <td><span class="status-pill blue">${item.murid?.kelas?.nama_kelas || '-'}</span></td>
                <td class="font-mono">${item.waktu_login || '-'}</td>
                <td><span class="status-pill amber">Terkunci di Perangkat</span></td>
                <td>
                    <button class="cbt-btn primary" onclick="eksekusiUnlock(${item.id_murid}, '${nama.replace(/'/g, "\\'")}')">
                        Reset Sesi / Unlock
                    </button>
                </td>
            </tr>
        `;
    });
}

async function eksekusiUnlock(idMurid, namaMurid) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin membuka kunci sesi login (Unlock) untuk murid <strong>${namaMurid}</strong>?<br><small>Progres jawaban dan sisa waktu ujian murid tetap aman.</small>`,
        'Konfirmasi Reset Sesi Login'
    );
    if (!yakin) return;

    const res = await cbtFetch(`/api/admin/reset-sessions/${idMurid}/unlock`, { method: 'POST' });
    const data = await res.json();
    await cbtAlert(data.message, 'Sesi Berhasil Dibuka', 'success');
    muatSesiTerkunci();
}