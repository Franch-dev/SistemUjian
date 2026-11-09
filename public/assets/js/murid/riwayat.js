/**
 * MURID/RIWAYAT.JS — RIWAYAT UJIAN & EVALUASI KKM (TANPA EMOTIKON)
 */
document.addEventListener('DOMContentLoaded', muatRiwayatNilaiMurid);

function formatStatusPenyelesaian(raw) {
    const map = {
        'selesai_mandiri': 'Selesai Mandiri',
        'auto_submit_waktu': 'Selesai (Waktu Habis)',
        'auto_submit_pelanggaran': 'Dihentikan (Batas Pelanggaran)',
        'diskualifikasi': 'Diskualifikasi',
        'mengerjakan': 'Sedang Mengerjakan'
    };
    return map[raw] || raw;
}

async function muatRiwayatNilaiMurid() {
    const res = await cbtFetch('/api/murid/riwayat-nilai');
    const json = await res.json();
    const list = json.data || [];

    const tbody = document.getElementById('tbodyRiwayatMurid');
    tbody.innerHTML = '';

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#64748B; padding:28px;">Belum ada riwayat ujian yang diselesaikan.</td></tr>`;
        return;
    }

    list.forEach(item => {
        const dipublikasi = item.publikasi_nilai && item.hasil;
        const isLulus = dipublikasi && item.hasil.status_kelulusan === 'Lulus';

        tbody.innerHTML += `
            <tr>
                <td>
                    <strong>${item.nama_mapel}</strong><br>
                    <small style="color:#64748B;">${item.nama_sesi_ujian}</small>
                </td>
                <td>${item.guru_pengampu}</td>
                <td><span class="status-pill slate font-mono">KKM ${item.nilai_kkm}</span></td>
                <td><span class="status-pill blue">${formatStatusPenyelesaian(item.status_ujian)}</span></td>
                <td class="font-mono">${dipublikasi ? `${item.hasil.jumlah_benar} Soal Benar` : '<span style="color:#64748B;">Disembunyikan</span>'}</td>
                <td class="font-mono"><strong style="font-size:14.5px; color:#0F172A;">${dipublikasi ? item.hasil.nilai_akhir : '-'}</strong></td>
                <td>
                    ${dipublikasi
                        ? `<span class="status-pill ${isLulus ? 'green' : 'red'}">${item.hasil.status_kelulusan.toUpperCase()}</span>`
                        : `<span class="status-pill amber">Menunggu Publikasi Admin</span>`
                    }
                </td>
            </tr>
        `;
    });
}