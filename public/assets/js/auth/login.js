/**
 * ==========================================================================
 * LOGIN.JS — LOGIKA AUTENTIKASI API & DETEKSI SINGLE DEVICE LOCK
 * Sistem Ujian Online SMK Negeri 1 Kota Bekasi
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('cbtLoginForm');
    const identifierInput = document.getElementById('identifierInput');
    const passwordInput = document.getElementById('passwordInput');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const contactAdminBtn = document.getElementById('contactAdminBtn');

    // 1. Toggle Lihat / Sembunyikan Password
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        });
    }

    // 2. Tombol Hubungi Admin (Bantuan Sesi Terkunci)
    if (contactAdminBtn) {
        contactAdminBtn.addEventListener('click', () => {
            showLoginAlert(
                '<strong>Bantuan Sesi Terkunci (Single Device Lock):</strong><br>' +
                'Jika akun Anda terkunci karena pindah perangkat atau browser tertutup mendadak, ' +
                'silakan lapor ke <strong>Pengawas Ruang / Administrator CBT</strong> untuk melakukan <em>Reset Session</em>.',
                'warning'
            );
        });
    }

    // 3. Submit Form Login ke REST API Laravel
    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            hideLoginAlert();

            const identifier = identifierInput.value.trim();
            const password = passwordInput.value;

            if (!identifier || !password) {
                showLoginAlert('Silakan isi Username/NIS dan Password terlebih dahulu.', 'error');
                return;
            }

            await attemptSmartLogin(identifier, password);
        });
    }
});

/**
 * Menentukan urutan pengecekan role secara cerdas berdasarkan pola Username / NIS,
 * sehingga tampilan form tetap bersih tanpa perlu dropdown role tambahan.
 */
function detectRolePriority(identifier) {
    const lower = identifier.toLowerCase();

    // Jika mengandung kata 'admin', prioritaskan role admin
    if (lower.includes('admin')) {
        return ['admin', 'guru', 'murid'];
    }

    // Jika kombinasi 6 huruf + 4 angka (Kode Guru, misal BUDISA4821)
    if (/^[A-Za-z]{6}\d{4}$/.test(identifier)) {
        return ['guru', 'murid', 'admin'];
    }

    // Default: Prioritaskan Murid (NIS / Nama Lengkap), lalu Guru, lalu Admin
    return ['murid', 'guru', 'admin'];
}

async function attemptSmartLogin(identifier, password) {
    const submitBtn = document.getElementById('submitLoginBtn');
    const originalBtnHtml = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Memproses...</span>';

    const rolesToTry = detectRolePriority(identifier);

    try {
        for (const role of rolesToTry) {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    role: role,
                    identifier: identifier,
                    password: password
                })
            });

            const result = await response.json();

            // Jika terkena proteksi Single Device Lock (HTTP 403)
            if (response.status === 403 && result.error_code === 'ERR_SINGLE_DEVICE_LOCK') {
                showLoginAlert(
                    '<strong>Akses Ditolak (Single Device Lock):</strong><br>' + result.message,
                    'warning'
                );
                return;
            }

            // Jika Login Berhasil (HTTP 200)
            if (response.ok && result.status === 'success') {
                localStorage.setItem('cbt_token', result.token);
                localStorage.setItem('cbt_role', result.role);
                localStorage.setItem('cbt_user', JSON.stringify(result.user));

                if (result.resume_exam) {
                    localStorage.setItem('cbt_resume_exam', JSON.stringify(result.resume_exam));
                }

                showLoginAlert('Login berhasil! Mengalihkan ke halaman utama...', 'success');

                setTimeout(() => {
                    if (result.role === 'admin') {
                        window.location.href = '/admin/dashboard';
                    } else if (result.role === 'guru') {
                        window.location.href = '/guru/dashboard';
                    } else {
                        window.location.href = '/murid/dashboard';
                    }
                }, 500);
                return;
            }
        }

        // Jika sudah dicoba di semua role namun tetap 401
        showLoginAlert('Username / NIS atau Password yang Anda masukkan salah.', 'error');

    } catch (error) {
        showLoginAlert('Gagal terhubung ke server ujian. Pastikan koneksi dan server aktif.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
    }
}

function showLoginAlert(htmlMessage, type = 'error') {
    const alertBox = document.getElementById('loginAlertBox');
    if (!alertBox) return;

    alertBox.className = 'cbt-alert show';
    if (type === 'warning') {
        alertBox.classList.add('alert-warning');
    } else if (type === 'success') {
        alertBox.classList.add('alert-success');
    } else {
        alertBox.classList.add('alert-error');
    }

    alertBox.innerHTML = htmlMessage;
}

function hideLoginAlert() {
    const alertBox = document.getElementById('loginAlertBox');
    if (alertBox) {
        alertBox.className = 'cbt-alert';
        alertBox.innerHTML = '';
    }
}