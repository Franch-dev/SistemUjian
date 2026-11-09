/**
 * ==========================================================================
 * ADMIN-AKUN.JS — CRUD MURID & GURU (DENGAN MODAL EDIT, SORTING, & POPUP)
 * ==========================================================================
 */

let STATE_MURID = [];
let STATE_GURU = [];

document.addEventListener('DOMContentLoaded', () => {
    muatKelasDropdown();
    ambilDataMurid();
    ambilDataGuru();
});

async function muatKelasDropdown() {
    const res = await cbtFetch('/api/admin/kelas');
    const json = await res.json();
    const selModal = document.getElementById('mKelas');
    const selFilter = document.getElementById('filterMuridKelas');

    selModal.innerHTML = '';
    selFilter.innerHTML = '<option value="">Semua Kelas</option>';

    (json.data || []).forEach(k => {
        selModal.innerHTML += `<option value="${k.id_kelas}">${k.nama_kelas}</option>`;
        selFilter.innerHTML += `<option value="${k.id_kelas}">${k.nama_kelas}</option>`;
    });
}

/* ==========================================================================
   1. MANAJEMEN AKUN MURID (TAMBAH, EDIT, SORTING, HAPUS)
   ========================================================================== */
async function ambilDataMurid() {
    const res = await cbtFetch('/api/admin/murid');
    const json = await res.json();
    STATE_MURID = json.data || [];
    renderTabelMurid();
}

function renderTabelMurid() {
    const keyword = (document.getElementById('searchMuridInput')?.value || '').toLowerCase();
    const idKelas = document.getElementById('filterMuridKelas')?.value || '';
    const sortBy = document.getElementById('sortMuridSelect')?.value || 'nama_asc';

    let list = STATE_MURID.filter(m => {
        const matchSearch = m.nama_lengkap.toLowerCase().includes(keyword) || m.nis.toLowerCase().includes(keyword);
        const matchKelas = !idKelas || String(m.id_kelas) === String(idKelas);
        return matchSearch && matchKelas;
    });

    list.sort((a, b) => {
        if (sortBy === 'nama_asc') return a.nama_lengkap.localeCompare(b.nama_lengkap);
        if (sortBy === 'nama_desc') return b.nama_lengkap.localeCompare(a.nama_lengkap);
        if (sortBy === 'nis_asc') return a.nis.localeCompare(b.nis);
        if (sortBy === 'status_locked') {
            const lockA = a.sesi_login?.is_login_locked ? 1 : 0;
            const lockB = b.sesi_login?.is_login_locked ? 1 : 0;
            return lockB - lockA;
        }
        return 0;
    });

    const tbody = document.getElementById('tbodyMurid');
    tbody.innerHTML = '';

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#64748B;">Data murid tidak ditemukan.</td></tr>`;
        return;
    }

    list.forEach(m => {
        const locked = m.sesi_login && m.sesi_login.is_login_locked;
        tbody.innerHTML += `
            <tr>
                <td class="font-mono"><strong>${m.nis}</strong></td>
                <td><strong>${m.nama_lengkap}</strong></td>
                <td><span class="status-pill blue">${m.kelas?.nama_kelas || '-'}</span></td>
                <td class="font-mono">${m.password_cetak || m.nis}</td>
                <td><span class="status-pill ${locked ? 'amber' : 'green'}">${locked ? 'Terkunci' : 'Terbuka'}</span></td>
                <td>
                    <div class="action-cell">
                        <button type="button" class="cbt-btn edit-btn" onclick="bukaModalEditMurid(${m.id_murid})">Edit</button>
                        <button type="button" class="cbt-btn danger" onclick="hapusMurid(${m.id_murid}, '${m.nama_lengkap.replace(/'/g, "\\'")}')">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function bukaModalTambahMurid() {
    document.getElementById('formModalMurid').reset();
    document.getElementById('editMuridId').value = '';
    document.getElementById('modalMuridTitle').textContent = 'Tambah Akun Murid Baru';
    openModal('modalMurid');
}

function bukaModalEditMurid(idMurid) {
    const m = STATE_MURID.find(x => x.id_murid === idMurid);
    if (!m) return;

    document.getElementById('editMuridId').value = m.id_murid;
    document.getElementById('mNis').value = m.nis;
    document.getElementById('mNama').value = m.nama_lengkap;
    document.getElementById('mKelas').value = m.id_kelas;
    document.getElementById('mPass').value = m.password_cetak || '';
    document.getElementById('modalMuridTitle').textContent = `Edit Data Murid — ${m.nama_lengkap}`;
    openModal('modalMurid');
}

function isiPassMuridOtomatis() {
    document.getElementById('mPass').value = generateRandomPass(8);
}

function resetPassMuridKeNis() {
    const nis = document.getElementById('mNis').value.trim();
    document.getElementById('mPass').value = nis || generateRandomPass(6);
}

async function simpanDataMurid(e) {
    e.preventDefault();
    const idEdit = document.getElementById('editMuridId').value;
    const nama = document.getElementById('mNama').value.trim();

    const yakin = await cbtConfirm(
        idEdit
            ? `Apakah Anda yakin ingin menyimpan perubahan data untuk murid <strong>${nama}</strong>?`
            : `Apakah Anda yakin ingin menambahkan murid baru bernama <strong>${nama}</strong>?`,
        idEdit ? 'Konfirmasi Edit Murid' : 'Konfirmasi Tambah Murid'
    );
    if (!yakin) return;

    const payload = {
        nis: document.getElementById('mNis').value.trim(),
        nama_lengkap: nama,
        id_kelas: document.getElementById('mKelas').value,
        password: document.getElementById('mPass').value.trim()
    };

    const url = idEdit ? `/api/admin/murid/${idEdit}` : '/api/admin/murid';
    const method = idEdit ? 'PUT' : 'POST';

    const res = await cbtFetch(url, { method, body: JSON.stringify(payload) });
    const data = await res.json();

    if (res.ok) {
        closeModal('modalMurid');
        await cbtAlert(data.message || 'Data murid berhasil disimpan.', 'Berhasil Disimpan', 'success');
        ambilDataMurid();
    } else {
        await cbtAlert(data.message || 'Gagal menyimpan data murid. Pastikan NIS unik.', 'Gagal Menyimpan', 'error');
    }
}

async function hapusMurid(id, nama) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghapus akun murid <strong>${nama}</strong> secara permanen?`,
        'Konfirmasi Hapus Murid',
        'danger'
    );
    if (!yakin) return;

    const res = await cbtFetch(`/api/admin/murid/${id}`, { method: 'DELETE' });
    const data = await res.json();
    await cbtAlert(data.message || 'Akun murid telah dihapus.', 'Data Dihapus', 'success');
    ambilDataMurid();
}

/* ==========================================================================
   2. MANAJEMEN AKUN GURU (TAMBAH, EDIT, GENERATE/RESET PASSWORD, HAPUS)
   ========================================================================== */
async function ambilDataGuru() {
    const res = await cbtFetch('/api/admin/guru');
    const json = await res.json();
    STATE_GURU = json.data || [];
    renderTabelGuru();
}

function renderTabelGuru() {
    const keyword = (document.getElementById('searchGuruInput')?.value || '').toLowerCase();
    const sortBy = document.getElementById('sortGuruSelect')?.value || 'nama_asc';

    let list = STATE_GURU.filter(g =>
        g.nama_lengkap.toLowerCase().includes(keyword) ||
        g.kode_guru.toLowerCase().includes(keyword) ||
        (g.nip && g.nip.toLowerCase().includes(keyword))
    );

    list.sort((a, b) => {
        if (sortBy === 'nama_asc') return a.nama_lengkap.localeCompare(b.nama_lengkap);
        if (sortBy === 'nama_desc') return b.nama_lengkap.localeCompare(a.nama_lengkap);
        if (sortBy === 'kode_asc') return a.kode_guru.localeCompare(b.kode_guru);
        if (sortBy === 'plotting_desc') return (b.plotting_mapel_count || 0) - (a.plotting_mapel_count || 0);
        return 0;
    });

    const tbody = document.getElementById('tbodyGuru');
    tbody.innerHTML = '';

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#64748B;">Data guru tidak ditemukan.</td></tr>`;
        return;
    }

    list.forEach(g => {
        tbody.innerHTML += `
            <tr>
                <td class="font-mono"><strong>${g.kode_guru}</strong></td>
                <td><strong>${g.nama_lengkap}</strong></td>
                <td class="font-mono">${g.nip || '-'}</td>
                <td><span class="status-pill blue">${g.plotting_mapel_count || 0} Kelas</span></td>
                <td>
                    <div class="action-cell">
                        <button type="button" class="cbt-btn edit-btn" onclick="bukaModalEditGuru(${g.id_guru})">Edit / Reset Pass</button>
                        <button type="button" class="cbt-btn danger" onclick="hapusGuru(${g.id_guru}, '${g.nama_lengkap.replace(/'/g, "\\'")}')">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function bukaModalTambahGuru() {
    document.getElementById('formModalGuru').reset();
    document.getElementById('editGuruId').value = '';
    document.getElementById('groupKodeGuruInfo').style.display = 'none';
    document.getElementById('modalGuruTitle').textContent = 'Tambah Akun Guru Pengampu';
    document.getElementById('labelGuruPass').textContent = 'Password Login (Kosongkan = Generate Otomatis)';
    openModal('modalGuru');
}

function bukaModalEditGuru(idGuru) {
    const g = STATE_GURU.find(x => x.id_guru === idGuru);
    if (!g) return;

    document.getElementById('editGuruId').value = g.id_guru;
    document.getElementById('groupKodeGuruInfo').style.display = 'block';
    document.getElementById('gKodeReadonly').value = g.kode_guru;
    document.getElementById('gNama').value = g.nama_lengkap;
    document.getElementById('gNip').value = g.nip || '';
    document.getElementById('gPass').value = '';
    document.getElementById('labelGuruPass').textContent = 'Password Baru (Kosongkan jika tidak ingin diubah)';
    document.getElementById('modalGuruTitle').textContent = `Edit Data Guru — ${g.nama_lengkap}`;
    openModal('modalGuru');
}

function generatePasswordGuru() {
    const newPass = 'GURU-' + generateRandomPass(5);
    document.getElementById('gPass').value = newPass;
}

async function simpanDataGuru(e) {
    e.preventDefault();
    const idEdit = document.getElementById('editGuruId').value;
    const nama = document.getElementById('gNama').value.trim();
    const passVal = document.getElementById('gPass').value.trim();

    const yakin = await cbtConfirm(
        idEdit
            ? `Apakah Anda yakin ingin memperbarui data akun guru <strong>${nama}</strong>?`
            : `Apakah Anda yakin ingin menambahkan guru baru <strong>${nama}</strong>?`,
        idEdit ? 'Konfirmasi Edit Guru' : 'Konfirmasi Tambah Guru'
    );
    if (!yakin) return;

    const payload = {
        nama_lengkap: nama,
        nip: document.getElementById('gNip').value.trim(),
    };
    if (passVal) {
        payload.password = passVal;
    }

    const url = idEdit ? `/api/admin/guru/${idEdit}` : '/api/admin/guru';
    const method = idEdit ? 'PUT' : 'POST';

    const res = await cbtFetch(url, { method, body: JSON.stringify(payload) });
    const data = await res.json();

    if (res.ok) {
        closeModal('modalGuru');
        if (!idEdit) {
            await cbtAlert(
                `Akun Guru <strong>${nama}</strong> berhasil dibuat!<br><br>` +
                `• Kode Guru (Username): <strong class="font-mono">${data.data.kode_guru}</strong><br>` +
                `• Password Login: <strong class="font-mono">${data.generated_password}</strong>`,
                'Akun Guru Berhasil Dibuat',
                'success'
            );
        } else {
            await cbtAlert(
                `Data guru <strong>${nama}</strong> berhasil diperbarui.` +
                (passVal ? `<br>Password baru telah di-reset menjadi: <strong class="font-mono">${passVal}</strong>` : ''),
                'Data Guru Diperbarui',
                'success'
            );
        }
        ambilDataGuru();
    } else {
        await cbtAlert(data.message || 'Gagal menyimpan data guru.', 'Gagal Menyimpan', 'error');
    }
}

async function hapusGuru(id, nama) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghapus akun guru <strong>${nama}</strong>?`,
        'Konfirmasi Hapus Guru',
        'danger'
    );
    if (!yakin) return;

    const res = await cbtFetch(`/api/admin/guru/${id}`, { method: 'DELETE' });
    const data = await res.json();
    await cbtAlert(data.message || 'Akun guru berhasil dihapus.', 'Guru Dihapus', 'success');
    ambilDataGuru();
}

async function importFileAkun(input, url) {
    if (!input.files.length) return;
    const namaFile = input.files[0].name;

    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin mengimpor data akun dari berkas <strong>${namaFile}</strong>?`,
        'Konfirmasi Import Excel/CSV'
    );
    if (!yakin) {
        input.value = '';
        return;
    }

    const fd = new FormData();
    fd.append('file', input.files[0]);
    const res = await cbtFetch(url, { method: 'POST', body: fd });
    const data = await res.json();
    input.value = '';

    await cbtAlert(data.message || 'Import selesai.', res.ok ? 'Import Berhasil' : 'Import Gagal', res.ok ? 'success' : 'error');
    ambilDataMurid();
    ambilDataGuru();
}