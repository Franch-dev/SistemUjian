/**
 * ==========================================================================
 * ADMIN-MASTER.JS — LOGIKA MASTER JURUSAN, KELAS, & MAPEL BERBASIS JURUSAN
 * ==========================================================================
 */

let LIST_JURUSAN = [];
let LIST_KELAS = [];
let LIST_MAPEL = [];

document.addEventListener('DOMContentLoaded', muatSemuaMaster);

async function muatSemuaMaster() {
    const [resJ, resK, resM] = await Promise.all([
        cbtFetch('/api/admin/jurusan'),
        cbtFetch('/api/admin/kelas'),
        cbtFetch('/api/admin/mapel')
    ]);
    const [jData, kData, mData] = await Promise.all([resJ.json(), resK.json(), resM.json()]);

    LIST_JURUSAN = jData.data || [];
    LIST_KELAS = kData.data || [];
    LIST_MAPEL = mData.data || [];

    // Isi dropdown jurusan pada Modal Kelas
    const selJ = document.getElementById('kJurusan');
    if (selJ) {
        selJ.innerHTML = '';
        LIST_JURUSAN.forEach(j => {
            selJ.innerHTML += `<option value="${j.id_jurusan}">${j.kode_jurusan} - ${j.nama_jurusan}</option>`;
        });
    }

    // Isi dropdown jurusan pada Modal Mapel & Filter Tabel Mapel
    const selMpJur = document.getElementById('mpJurusanKode');
    const filMpJur = document.getElementById('filterMapelJurusan');
    if (selMpJur && filMpJur) {
        selMpJur.innerHTML = '<option value="UMUM">UMUM (Semua Jurusan — MTK, B.Indo, dll)</option>';
        filMpJur.innerHTML = '<option value="">Semua Jurusan & Umum</option><option value="UMUM">Mapel Umum (Nasional)</option>';
        LIST_JURUSAN.forEach(j => {
            selMpJur.innerHTML += `<option value="${j.kode_jurusan}">${j.kode_jurusan} — ${j.nama_jurusan}</option>`;
            filMpJur.innerHTML += `<option value="${j.kode_jurusan}">Jurusan ${j.kode_jurusan}</option>`;
        });
    }

    renderJurusan();
    renderKelas();
    renderMapel();
}

/* ==========================================================================
   1. MASTER JURUSAN
   ========================================================================== */
function renderJurusan() {
    const q = (document.getElementById('searchJurusan')?.value || '').toLowerCase();
    const sort = document.getElementById('sortJurusan')?.value || 'kode_asc';

    let rows = LIST_JURUSAN.filter(j =>
        j.kode_jurusan.toLowerCase().includes(q) || j.nama_jurusan.toLowerCase().includes(q)
    );
    rows.sort((a, b) => sort === 'nama_asc'
        ? a.nama_jurusan.localeCompare(b.nama_jurusan)
        : a.kode_jurusan.localeCompare(b.kode_jurusan)
    );

    const tb = document.getElementById('tbodyJurusan');
    tb.innerHTML = '';
    rows.forEach(j => {
        tb.innerHTML += `
            <tr>
                <td class="font-mono"><strong>${j.kode_jurusan}</strong></td>
                <td>${j.nama_jurusan}</td>
                <td>
                    <div class="action-cell">
                        <button class="cbt-btn edit-btn" onclick="bukaEditJurusan(${j.id_jurusan})">Edit</button>
                        <button class="cbt-btn danger" onclick="hapusJurusan(${j.id_jurusan}, '${j.nama_jurusan.replace(/'/g, "\\'")}')">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function bukaModalJurusan() {
    document.getElementById('editJurusanId').value = '';
    document.getElementById('jKode').value = '';
    document.getElementById('jNama').value = '';
    document.getElementById('titleModalJurusan').textContent = 'Tambah Jurusan Baru';
    openModal('modalJurusan');
}

function bukaEditJurusan(id) {
    const j = LIST_JURUSAN.find(x => x.id_jurusan === id);
    if (!j) return;
    document.getElementById('editJurusanId').value = j.id_jurusan;
    document.getElementById('jKode').value = j.kode_jurusan;
    document.getElementById('jNama').value = j.nama_jurusan;
    document.getElementById('titleModalJurusan').textContent = 'Edit Jurusan';
    openModal('modalJurusan');
}

async function simpanJurusan(e) {
    e.preventDefault();
    const id = document.getElementById('editJurusanId').value;
    const nama = document.getElementById('jNama').value;
    if (!(await cbtConfirm(`Yakin ingin menyimpan data jurusan <strong>${nama}</strong>?`))) return;

    const res = await cbtFetch(id ? `/api/admin/jurusan/${id}` : '/api/admin/jurusan', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify({ kode_jurusan: document.getElementById('jKode').value, nama_jurusan: nama })
    });
    const data = await res.json();
    closeModal('modalJurusan');
    await cbtAlert(data.message, res.ok ? 'Berhasil' : 'Gagal', res.ok ? 'success' : 'error');
    muatSemuaMaster();
}

async function hapusJurusan(id, nama) {
    if (!(await cbtConfirm(`Yakin ingin menghapus jurusan <strong>${nama}</strong>?`, 'Hapus Jurusan', 'danger'))) return;
    await cbtFetch(`/api/admin/jurusan/${id}`, { method: 'DELETE' });
    await cbtAlert('Jurusan berhasil dihapus.');
    muatSemuaMaster();
}

/* ==========================================================================
   2. MASTER KELAS
   ========================================================================== */
function renderKelas() {
    const q = (document.getElementById('searchKelas')?.value || '').toLowerCase();
    const sort = document.getElementById('sortKelas')?.value || 'nama_asc';

    let rows = LIST_KELAS.filter(k => k.nama_kelas.toLowerCase().includes(q));
    rows.sort((a, b) => sort === 'murid_desc'
        ? (b.murid_count || 0) - (a.murid_count || 0)
        : a.nama_kelas.localeCompare(b.nama_kelas)
    );

    const tb = document.getElementById('tbodyKelas');
    tb.innerHTML = '';
    rows.forEach(k => {
        tb.innerHTML += `
            <tr>
                <td><strong>${k.nama_kelas}</strong></td>
                <td>${k.jurusan?.nama_jurusan || '-'}</td>
                <td><span class="status-pill blue">${k.murid_count || 0} Siswa</span></td>
                <td>
                    <div class="action-cell">
                        <button class="cbt-btn edit-btn" onclick="bukaEditKelas(${k.id_kelas})">Edit</button>
                        <button class="cbt-btn danger" onclick="hapusKelas(${k.id_kelas}, '${k.nama_kelas}')">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function bukaModalKelas() {
    document.getElementById('editKelasId').value = '';
    document.getElementById('kRombel').value = '';
    document.getElementById('titleModalKelas').textContent = 'Tambah Kelas Baru';
    openModal('modalKelas');
}

function bukaEditKelas(id) {
    const k = LIST_KELAS.find(x => x.id_kelas === id);
    if (!k) return;
    document.getElementById('editKelasId').value = k.id_kelas;
    document.getElementById('kTingkat').value = k.tingkat;
    document.getElementById('kJurusan').value = k.id_jurusan;
    document.getElementById('kRombel').value = k.kode_rombel;
    document.getElementById('titleModalKelas').textContent = `Edit Kelas — ${k.nama_kelas}`;
    openModal('modalKelas');
}

async function simpanKelas(e) {
    e.preventDefault();
    const id = document.getElementById('editKelasId').value;
    if (!(await cbtConfirm('Apakah Anda yakin ingin menyimpan konfigurasi kelas ini?'))) return;

    const res = await cbtFetch(id ? `/api/admin/kelas/${id}` : '/api/admin/kelas', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify({
            tingkat: document.getElementById('kTingkat').value,
            id_jurusan: document.getElementById('kJurusan').value,
            kode_rombel: document.getElementById('kRombel').value
        })
    });
    const data = await res.json();
    closeModal('modalKelas');
    await cbtAlert(data.message, res.ok ? 'Berhasil' : 'Gagal', res.ok ? 'success' : 'error');
    muatSemuaMaster();
}

async function hapusKelas(id, nama) {
    if (!(await cbtConfirm(`Yakin ingin menghapus kelas <strong>${nama}</strong>?`, 'Hapus Kelas', 'danger'))) return;
    await cbtFetch(`/api/admin/kelas/${id}`, { method: 'DELETE' });
    await cbtAlert('Data kelas berhasil dihapus.');
    muatSemuaMaster();
}

/* ==========================================================================
   3. MASTER MAPEL (DENGAN PENGUNCIAN PREFIX JURUSAN & TINGKAT KELAS)
   ========================================================================== */
function parseInfoKodeMapel(kode) {
    const raw = (kode || '').toUpperCase().trim();
    const parts = raw.split('-');
    const validTingkat = ['X', 'XI', 'XII', 'ALL'];

    // Format resmi: {JURUSAN}-{TINGKAT}-{SINGKATAN} (contoh: RPL-XII-PEMWEB)
    if (parts.length >= 3 && validTingkat.includes(parts[1])) {
        return {
            jurusan: parts[0],
            tingkat: parts[1],
            singkatan: parts.slice(2).join('-')
        };
    }

    // Jika format lama dari seeder (misal MAPEL-BD-01 atau PEMWEB)
    const fallbackShort = raw.replace(/^MAPEL-/, '');
    return {
        jurusan: 'UMUM',
        tingkat: 'ALL',
        singkatan: fallbackShort || 'MP01'
    };
}

function isiSingkatanOtomatis() {
    // Hanya buat singkatan otomatis saat tambah baru jika kotak singkatan belum diketik manual
    const idEdit = document.getElementById('editMapelId').value;
    if (!idEdit) {
        const nama = document.getElementById('mpNama').value.trim();
        const words = nama.split(/\s+/).filter(Boolean);
        let abbr = '';
        if (words.length === 1) {
            abbr = words[0].substring(0, 6).toUpperCase();
        } else {
            abbr = words.map(w => w[0]).join('').toUpperCase().substring(0, 6);
        }
        document.getElementById('mpSingkatan').value = abbr;
    }
    updatePreviewKodeMapel();
}

function updatePreviewKodeMapel() {
    const jur = (document.getElementById('mpJurusanKode').value || 'UMUM').toUpperCase();
    const tkt = (document.getElementById('mpTingkat').value || 'ALL').toUpperCase();
    let shortCode = (document.getElementById('mpSingkatan').value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');

    if (!shortCode) shortCode = 'MP01';

    // Jika user tidak sengaja mengetik ulang prefix jurusan di kolom singkatan, bersihkan otomatis
    const fullCode = `${jur}-${tkt}-${shortCode}`;

    document.getElementById('mpPrefixBadge').textContent = `${jur}-${tkt}-`;
    document.getElementById('mpKodeFullPreview').textContent = fullCode;
    document.getElementById('mpKode').value = fullCode;
}

function renderMapel() {
    const q = (document.getElementById('searchMapel')?.value || '').toLowerCase();
    const fJur = document.getElementById('filterMapelJurusan')?.value || '';
    const fTkt = document.getElementById('filterMapelTingkat')?.value || '';
    const sort = document.getElementById('sortMapel')?.value || 'nama_asc';

    let rows = LIST_MAPEL.filter(m => {
        const info = parseInfoKodeMapel(m.kode_mapel);
        const matchQ = m.nama_mapel.toLowerCase().includes(q) ||
                       m.kode_mapel.toLowerCase().includes(q) ||
                       info.jurusan.toLowerCase().includes(q);
        const matchJur = !fJur || info.jurusan === fJur;
        const matchTkt = !fTkt || info.tingkat === fTkt || info.tingkat === 'ALL';
        return matchQ && matchJur && matchTkt;
    });

    rows.sort((a, b) => {
        if (sort === 'kkm_desc') return b.nilai_kkm - a.nilai_kkm;
        if (sort === 'kkm_asc') return a.nilai_kkm - b.nilai_kkm;
        return a.nama_mapel.localeCompare(b.nama_mapel);
    });

    const tb = document.getElementById('tbodyMapel');
    tb.innerHTML = '';

    if (rows.length === 0) {
        tb.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#64748B;">Tidak ada mata pelajaran untuk filter ini.</td></tr>`;
        return;
    }

    rows.forEach(m => {
        const info = parseInfoKodeMapel(m.kode_mapel);
        const labelTkt = info.tingkat === 'ALL' ? 'Semua Kelas' : `Kelas ${info.tingkat}`;
        tb.innerHTML += `
            <tr>
                <td class="font-mono"><strong>${m.kode_mapel}</strong></td>
                <td>
                    <span class="status-pill blue">${info.jurusan}</span>
                    <span class="status-pill amber">${labelTkt}</span>
                </td>
                <td><strong>${m.nama_mapel}</strong></td>
                <td><span class="status-pill green">KKM ${m.nilai_kkm}</span></td>
                <td>
                    <div class="action-cell">
                        <button class="cbt-btn edit-btn" onclick="bukaEditMapel(${m.id_mapel})">Edit</button>
                        <button class="cbt-btn danger" onclick="hapusMapel(${m.id_mapel}, '${m.nama_mapel.replace(/'/g, "\\'")}')">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function bukaModalMapel() {
    document.getElementById('editMapelId').value = '';
    document.getElementById('mpJurusanKode').value = 'UMUM';
    document.getElementById('mpTingkat').value = 'ALL';
    document.getElementById('mpNama').value = '';
    document.getElementById('mpSingkatan').value = '';
    document.getElementById('mpKkm').value = 75;
    updatePreviewKodeMapel();
    document.getElementById('titleModalMapel').textContent = 'Tambah Mata Pelajaran Baru';
    openModal('modalMapel');
}

function bukaEditMapel(id) {
    const m = LIST_MAPEL.find(x => x.id_mapel === id);
    if (!m) return;
    const info = parseInfoKodeMapel(m.kode_mapel);

    document.getElementById('editMapelId').value = m.id_mapel;
    document.getElementById('mpJurusanKode').value = info.jurusan;
    document.getElementById('mpTingkat').value = info.tingkat;
    document.getElementById('mpNama').value = m.nama_mapel;
    document.getElementById('mpSingkatan').value = info.singkatan;
    document.getElementById('mpKkm').value = m.nilai_kkm;
    updatePreviewKodeMapel();

    document.getElementById('titleModalMapel').textContent = `Edit Mapel — ${m.nama_mapel}`;
    openModal('modalMapel');
}

async function simpanMapel(e) {
    e.preventDefault();
    updatePreviewKodeMapel(); // Pastikan kode lengkap selalu ter-update sebelum dikirim

    const id = document.getElementById('editMapelId').value;
    const nama = document.getElementById('mpNama').value.trim();
    const kodeFull = document.getElementById('mpKode').value.trim();
    const jur = document.getElementById('mpJurusanKode').value;
    const tkt = document.getElementById('mpTingkat').value;

    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menyimpan mata pelajaran <strong>${nama}</strong> untuk jurusan <strong>${jur}</strong> (${tkt === 'ALL' ? 'Semua Tingkat' : 'Kelas ' + tkt}) dengan kode <strong class="font-mono">${kodeFull}</strong>?`,
        'Konfirmasi Simpan Mapel'
    );
    if (!yakin) return;

    const res = await cbtFetch(id ? `/api/admin/mapel/${id}` : '/api/admin/mapel', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify({
            kode_mapel: kodeFull,
            nama_mapel: nama,
            nilai_kkm: document.getElementById('mpKkm').value
        })
    });
    const data = await res.json();
    closeModal('modalMapel');
    await cbtAlert(data.message, res.ok ? 'Berhasil Disimpan' : 'Gagal Menyimpan', res.ok ? 'success' : 'error');
    muatSemuaMaster();
}

async function hapusMapel(id, nama) {
    if (!(await cbtConfirm(`Yakin ingin menghapus mata pelajaran <strong>${nama}</strong>?`, 'Hapus Mapel', 'danger'))) return;
    await cbtFetch(`/api/admin/mapel/${id}`, { method: 'DELETE' });
    await cbtAlert('Mata pelajaran berhasil dihapus.');
    muatSemuaMaster();
}