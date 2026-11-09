document.addEventListener('DOMContentLoaded', muatDashboardGuru);

async function muatDashboardGuru() {
    const res = await cbtFetch('/api/guru/plotting-ampuan');
    const json = await res.json();

    if (json.guru) {
        document.getElementById('welcomeGuruName').textContent = json.guru.nama_lengkap;
        document.getElementById('guruKodeBadge').textContent = `KODE GURU: ${json.guru.kode_guru}`;
    }

    const list = json.data || [];
    let totalSoal = 0;
    let totalMurid = 0;
    let totalSesi = 0;

    const tbody = document.getElementById('tbodyKelasAmpuan');
    tbody.innerHTML = '';

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#64748B;">Belum ada kelas & mata pelajaran yang di-plotting untuk akun Anda oleh Admin.</td></tr>`;
    }

    list.forEach(p => {
        const jmlSoal = p.bank_soal_count || 0;
        const jmlMurid = (p.kelas?.murid || []).length;
        const jmlSesi = (p.sesi_ujian || []).length;

        totalSoal += jmlSoal;
        totalMurid += jmlMurid;
        totalSesi += jmlSesi;

        tbody.innerHTML += `
            <tr>
                <td>
                    <span class="status-pill blue">${p.kelas?.nama_kelas || '-'}</span><br>
                    <small style="color:#64748B;">${p.kelas?.jurusan?.nama_jurusan || ''}</small>
                </td>
                <td>
                    <strong>${p.mapel?.nama_mapel || '-'}</strong><br>
                    <small class="font-mono" style="color:#64748B;">${p.mapel?.kode_mapel || ''}</small>
                </td>
                <td><span class="status-pill green">KKM ${p.mapel?.nilai_kkm || 75}</span></td>
                <td><strong>${jmlMurid} Siswa</strong></td>
                <td><span class="status-pill ${jmlSoal > 0 ? 'blue' : 'amber'}">${jmlSoal} Butir Soal</span></td>
                <td>
                    <div class="action-cell">
                        <a href="/guru/bank-soal?id_plotting=${p.id_plotting}" class="cbt-btn primary" style="text-decoration:none;">Kelola Soal</a>
                        <a href="/guru/rekap-nilai?id_plotting=${p.id_plotting}" class="cbt-btn outline" style="text-decoration:none;">Rekap Nilai</a>
                    </div>
                </td>
            </tr>
        `;
    });

    document.getElementById('kpiGuruKelas').textContent = list.length;
    document.getElementById('kpiGuruSoal').textContent = totalSoal;
    document.getElementById('kpiGuruMurid').textContent = totalMurid;
    document.getElementById('kpiGuruSesi').textContent = totalSesi;
}