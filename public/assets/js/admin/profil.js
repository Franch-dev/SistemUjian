document.addEventListener('DOMContentLoaded', muatProfilAdmin);

async function muatProfilAdmin() {
    const res = await cbtFetch('/api/admin/profil-akun');
    const json = await res.json();
    if (json.data) {
        document.getElementById('adminUsernameInput').value = json.data.username || '';
        document.getElementById('adminLastLoginInput').value = json.data.login_terakhir || 'Baru saja aktif';
    }
}

function generatePassAdmin() {
    document.getElementById('adminNewPassword').value = 'ADM-' + generateRandomPass(6);
}

async function simpanProfilAdmin(e) {
    e.preventDefault();
    const username = document.getElementById('adminUsernameInput').value.trim();
    const password = document.getElementById('adminNewPassword').value.trim();

    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin memperbarui profil Administrator menjadi <strong>${username}</strong>?`,
        'Konfirmasi Update Profil Admin'
    );
    if (!yakin) return;

    const payload = { username };
    if (password) payload.password = password;

    const res = await cbtFetch('/api/admin/profil-akun', {
        method: 'PUT',
        body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (res.ok) {
        localStorage.setItem('cbt_user', JSON.stringify(data.data));
        document.getElementById('sidebarAdminName').textContent = data.data.username;
        document.getElementById('adminNewPassword').value = '';
        await cbtAlert(
            data.message + (password ? `<br>Password baru Anda: <strong class="font-mono">${password}</strong>` : ''),
            'Profil Diperbarui',
            'success'
        );
    } else {
        await cbtAlert(data.message || 'Gagal memperbarui profil admin.', 'Gagal', 'error');
    }
}