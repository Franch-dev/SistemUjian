/**
 * ==========================================================================
 * ADMIN/COMMON.JS — FUNGSI GLOBAL, CUSTOM POPUP, JAM REAL-TIME & LOGOUT
 * ==========================================================================
 */

const CBT_TOKEN = localStorage.getItem('cbt_token');
const CBT_ROLE = localStorage.getItem('cbt_role');

if (!CBT_TOKEN || CBT_ROLE !== 'admin') {
    window.location.href = '/login';
}

document.addEventListener('DOMContentLoaded', () => {
    injectGlobalPopupContainer();
    initKalenderDanJamIndonesia();

    const userRaw = localStorage.getItem('cbt_user');
    if (userRaw) {
        const user = JSON.parse(userRaw);
        const nameEl = document.getElementById('sidebarAdminName');
        if (nameEl && user.username) {
            nameEl.textContent = user.username === 'admin_smkn1bks' ? 'Andika Permana' : user.username;
        }
    }

    refreshSidebarLockedBadge();
});

/**
 * Kalender Tahun Ajaran Indonesia & Jam Real-Time (WIB)
 * Dipanggil langsung di dalam DOMContentLoaded utama
 */
function initKalenderDanJamIndonesia() {
    const elTahunAjaran = document.getElementById('topbarAcademicYearText');
    const elTanggalJam = document.getElementById('topbarDateTimeText');

    const namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    function updateWaktu() {
        const now = new Date();
        const tahun = now.getFullYear();
        const bulanIndex = now.getMonth(); // 0 = Januari, 6 = Juli, 9 = Oktober

        const tahunAjaran = bulanIndex >= 6 ? `${tahun}/${tahun + 1}` : `${tahun - 1}/${tahun}`;
        const semester = bulanIndex >= 6 ? 'Semester Ganjil' : 'Semester Genap';

        if (elTahunAjaran) {
            elTahunAjaran.textContent = `${tahunAjaran} - ${semester}`;
        }

        if (elTanggalJam) {
            const hariStr = namaHari[now.getDay()];
            const tglStr = String(now.getDate()).padStart(2, '0');
            const blnStr = namaBulan[bulanIndex];
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');

            elTanggalJam.textContent = `${hariStr}, ${tglStr} ${blnStr} ${tahun} • ${jam}:${menit}:${detik} WIB`;
        }
    }

    updateWaktu();
    setInterval(updateWaktu, 1000);
}

function injectGlobalPopupContainer() {
    if (document.getElementById('cbtGlobalDialog')) return;

    const dialogHtml = `
        <div id="cbtGlobalDialog" class="cbt-modal-backdrop" style="z-index: 2000;">
            <div class="cbt-modal-box confirm-box">
                <div id="cbtDialogIcon" class="confirm-icon-circle question"></div>
                <h4 id="cbtDialogTitle" class="confirm-title">Konfirmasi Tindakan</h4>
                <p id="cbtDialogMessage" class="confirm-desc"></p>
                <div id="cbtDialogButtons" class="confirm-btn-row"></div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', dialogHtml);
}

function cbtConfirm(message, title = 'Konfirmasi Tindakan', type = 'question') {
    return new Promise((resolve) => {
        const backdrop = document.getElementById('cbtGlobalDialog');
        const iconBox = document.getElementById('cbtDialogIcon');
        const titleEl = document.getElementById('cbtDialogTitle');
        const msgEl = document.getElementById('cbtDialogMessage');
        const btnRow = document.getElementById('cbtDialogButtons');

        iconBox.className = `confirm-icon-circle ${type === 'danger' ? 'danger' : 'question'}`;
        iconBox.innerHTML = type === 'danger'
            ? `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`
            : `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;

        titleEl.textContent = title;
        msgEl.innerHTML = message;

        const confirmBtnClass = type === 'danger' ? 'danger' : 'primary';
        btnRow.innerHTML = `
            <button type="button" class="cbt-btn outline" id="cbtConfirmCancelBtn">Batal</button>
            <button type="button" class="cbt-btn ${confirmBtnClass}" id="cbtConfirmOkBtn">Ya, Lanjutkan</button>
        `;

        backdrop.classList.add('show');

        document.getElementById('cbtConfirmCancelBtn').onclick = () => {
            backdrop.classList.remove('show');
            resolve(false);
        };
        document.getElementById('cbtConfirmOkBtn').onclick = () => {
            backdrop.classList.remove('show');
            resolve(true);
        };
    });
}

function cbtAlert(message, title = 'Informasi Sistem', type = 'success') {
    return new Promise((resolve) => {
        const backdrop = document.getElementById('cbtGlobalDialog');
        const iconBox = document.getElementById('cbtDialogIcon');
        const titleEl = document.getElementById('cbtDialogTitle');
        const msgEl = document.getElementById('cbtDialogMessage');
        const btnRow = document.getElementById('cbtDialogButtons');

        iconBox.className = `confirm-icon-circle ${type === 'error' ? 'danger' : 'success'}`;
        iconBox.innerHTML = type === 'error'
            ? `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`
            : `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;

        titleEl.textContent = title;
        msgEl.innerHTML = message;

        btnRow.innerHTML = `
            <button type="button" class="cbt-btn primary" id="cbtAlertOkBtn">Mengerti (OK)</button>
        `;

        backdrop.classList.add('show');

        document.getElementById('cbtAlertOkBtn').onclick = () => {
            backdrop.classList.remove('show');
            resolve(true);
        };
    });
}

function openModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.classList.add('show');
}

function closeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.classList.remove('show');
}

function generateRandomPass(length = 8) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let res = '';
    for (let i = 0; i < length; i++) {
        res += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return res;
}

async function cbtFetch(url, options = {}) {
    const headers = {
        'Accept': 'application/json',
        'Authorization': `Bearer ${CBT_TOKEN}`,
        ...(options.headers || {})
    };

    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, { ...options, headers });
    if (response.status === 401) {
        localStorage.clear();
        window.location.href = '/login';
    }
    return response;
}

async function cbtDownloadFile(url, filename) {
    const res = await fetch(url, {
        method: 'GET',
        headers: { 'Authorization': `Bearer ${CBT_TOKEN}` }
    });
    if (!res.ok) {
        await cbtAlert('Gagal mengunduh berkas dari server.', 'Unduhan Gagal', 'error');
        return;
    }
    const blob = await res.blob();
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
}

async function refreshSidebarLockedBadge() {
    try {
        const res = await cbtFetch('/api/admin/reset-sessions');
        const json = await res.json();
        const badge = document.getElementById('sidebarLockedBadge');
        if (badge && json.data) {
            badge.textContent = json.data.length;
        }
    } catch (e) {}
}

function openSidebarMobile() {
    document.getElementById('cbtSidebar').classList.add('open');
    document.getElementById('mobileOverlay').classList.add('show');
}

function closeSidebarMobile() {
    document.getElementById('cbtSidebar').classList.remove('open');
    document.getElementById('mobileOverlay').classList.remove('show');
}

async function handleLogoutAdmin() {
    const yakin = await cbtConfirm(
        'Apakah Anda yakin ingin keluar (Logout) dari Portal Admin CBT SMK Negeri 1 Kota Bekasi?',
        'Konfirmasi Logout',
        'danger'
    );
    if (!yakin) return;

    await cbtFetch('/api/auth/logout', { method: 'POST' });
    localStorage.clear();
    window.location.href = '/login';
}