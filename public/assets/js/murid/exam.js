/**
 * ==========================================================================
 * MURID/EXAM.JS — MESIN LEMBAR UJIAN BERSIH, AUTO-SAVE, TIMER, & ANTI-CHEAT
 * ==========================================================================
 */

let ID_UJIAN_MURID = null;
let DAFTAR_SOAL = [];
let CURRENT_INDEX = 0;
let SISA_DETIK = 0;
let TIMER_INTERVAL = null;
let EXAM_FINISHED = false;
let LAST_CHEAT_TIMESTAMP = 0;

document.addEventListener('DOMContentLoaded', () => {
    ID_UJIAN_MURID = document.getElementById('activeIdUjianMurid').value;

    const userRaw = localStorage.getItem('cbt_user');
    if (userRaw) {
        const u = JSON.parse(userRaw);
        document.getElementById('examHeaderStudent').textContent = `${u.nama_lengkap} (${u.nis}) — ${u.kelas || ''}`;
    }

    muatLembarSoalDariServer();
    initSensorAntiCheat();
});

async function muatLembarSoalDariServer() {
    const res = await cbtFetch(`/api/murid/ujian/${ID_UJIAN_MURID}/lembar-soal`);
    const json = await res.json();

    if (!res.ok) {
        await cbtAlert(json.message || 'Sesi ujian sudah tidak aktif.', 'Akses Ditolak', 'error');
        window.location.href = '/murid/dashboard';
        return;
    }

    document.getElementById('examHeaderMapel').textContent = json.nama_mapel;
    document.getElementById('violationCounterText').textContent = `${json.jumlah_pelanggaran} / ${json.batas_toleransi_anticheat}`;

    SISA_DETIK = Number(json.sisa_waktu_detik || 0);
    DAFTAR_SOAL = json.lembar_soal || [];

    if (DAFTAR_SOAL.length === 0) {
        await cbtAlert('Belum ada butir soal pada mata pelajaran ini.', 'Soal Kosong', 'error');
        window.location.href = '/murid/dashboard';
        return;
    }

    renderNomorSoalGrid();
    tampilkanSoalKe(0);
    jalankanCountdownTimer();

    if (!document.fullscreenElement) {
        document.getElementById('fullscreenBlockerOverlay').classList.add('show');
    }
}

function tampilkanSoalKe(index) {
    if (index < 0 || index >= DAFTAR_SOAL.length) return;
    CURRENT_INDEX = index;

    const soal = DAFTAR_SOAL[CURRENT_INDEX];
    document.getElementById('currentNoBadge').textContent = `PERTANYAAN ${soal.nomor_urut} DARI ${DAFTAR_SOAL.length}`;
    document.getElementById('questionStemText').innerHTML = soal.pertanyaan;

    const imgBox = document.getElementById('questionStemImageBox');
    const imgEl = document.getElementById('questionStemImg');
    if (soal.url_gambar) {
        imgBox.style.display = 'block';
        imgEl.src = soal.url_gambar;
    } else {
        imgBox.style.display = 'none';
        imgEl.src = '';
    }

    const optContainer = document.getElementById('optionsContainer');
    optContainer.innerHTML = '';

    ['A', 'B', 'C', 'D', 'E'].forEach(letter => {
        const rawOpsi = soal.opsi[letter] || '-';
        const isSelected = soal.pilihan_jawaban === letter;

        optContainer.innerHTML += `
            <div class="option-choice-card ${isSelected ? 'selected' : ''}" onclick="pilihJawabanOpsi('${letter}')">
                <div class="option-letter-circle font-mono">${letter}</div>
                <div class="option-content-body">${formatKontenOpsiMurid(rawOpsi)}</div>
            </div>
        `;
    });

    document.getElementById('chkStatusRagu').checked = Boolean(soal.status_ragu);
    document.getElementById('btnPrevSoal').disabled = (CURRENT_INDEX === 0);
    document.getElementById('btnNextSoal').disabled = (CURRENT_INDEX === DAFTAR_SOAL.length - 1);

    renderNomorSoalGrid();
}

function formatKontenOpsiMurid(rawText) {
    if (!rawText) return '-';
    const match = rawText.match(/\[IMG:(.*?)\]/);
    const cleanText = rawText.replace(/\[IMG:.*?\]/g, '').trim();
    if (match && match[1]) {
        return `${cleanText ? `<div>${cleanText}</div>` : ''}<img src="${match[1]}" style="max-height:110px; border-radius:8px; margin-top:6px; border:1px solid #CBD5E1;">`;
    }
    return cleanText || '-';
}

function renderNomorSoalGrid() {
    const grid = document.getElementById('numberGridContainer');
    grid.innerHTML = '';

    let answeredCount = 0;

    DAFTAR_SOAL.forEach((s, idx) => {
        if (s.pilihan_jawaban) answeredCount++;

        let cls = 'num-box-btn font-mono';
        if (s.status_ragu) cls += ' doubtful';
        else if (s.pilihan_jawaban) cls += ' answered';
        if (idx === CURRENT_INDEX) cls += ' current';

        grid.innerHTML += `
            <button type="button" class="${cls}" onclick="tampilkanSoalKe(${idx})">
                ${s.nomor_urut}
            </button>
        `;
    });

    const total = DAFTAR_SOAL.length || 1;
    const pct = Math.round((answeredCount / total) * 100);
    const progText = document.getElementById('answeredProgressText');
    const progBar = document.getElementById('answeredProgressBar');

    if (progText) progText.textContent = `${answeredCount}/${DAFTAR_SOAL.length} Terjawab`;
    if (progBar) progBar.style.width = `${pct}%`;
}

function navigasiSoal(step) {
    tampilkanSoalKe(CURRENT_INDEX + step);
}

async function pilihJawabanOpsi(huruf) {
    if (EXAM_FINISHED) return;
    const soal = DAFTAR_SOAL[CURRENT_INDEX];
    soal.pilihan_jawaban = huruf;
    tampilkanSoalKe(CURRENT_INDEX);
    await kirimAutoSaveKeServer(soal);
}

async function toggleRaguRaguSoalIni() {
    if (EXAM_FINISHED) return;
    const soal = DAFTAR_SOAL[CURRENT_INDEX];
    soal.status_ragu = document.getElementById('chkStatusRagu').checked;
    renderNomorSoalGrid();
    await kirimAutoSaveKeServer(soal);
}

async function kirimAutoSaveKeServer(soal) {
    const ind = document.getElementById('autosaveStatusText');
    ind.textContent = 'Menyimpan jawaban...';

    try {
        const res = await cbtFetch(`/api/murid/ujian/${ID_UJIAN_MURID}/autosave`, {
            method: 'POST',
            body: JSON.stringify({
                id_soal: soal.id_soal,
                pilihan_jawaban: soal.pilihan_jawaban,
                status_ragu: Boolean(soal.status_ragu),
                sisa_waktu_detik: SISA_DETIK
            })
        });
        const data = await res.json();
        if (res.ok) {
            ind.textContent = `Tersimpan otomatis (${data.waktu_autosave})`;
        }
    } catch (e) {
        ind.textContent = 'Gangguan jaringan penyimpanan';
    }
}

function jalankanCountdownTimer() {
    perbaruiTampilanTimer();
    TIMER_INTERVAL = setInterval(async () => {
        if (EXAM_FINISHED) {
            clearInterval(TIMER_INTERVAL);
            return;
        }

        SISA_DETIK--;
        perbaruiTampilanTimer();

        if (SISA_DETIK <= 0) {
            clearInterval(TIMER_INTERVAL);
            EXAM_FINISHED = true;
            await eksekusiSubmitKeServer('auto_submit_waktu', 'Waktu pengerjaan ujian telah habis. Sistem mengumpulkan lembar jawaban Anda secara otomatis.');
        }
    }, 1000);
}

function perbaruiTampilanTimer() {
    const s = Math.max(0, SISA_DETIK);
    const jam = Math.floor(s / 3600);
    const mnt = Math.floor((s % 3600) / 60);
    const dtk = s % 60;

    const timerBox = document.getElementById('examTimerBox');
    timerBox.textContent = `${String(jam).padStart(2, '0')}:${String(mnt).padStart(2, '0')}:${String(dtk).padStart(2, '0')}`;

    if (s <= 300) {
        timerBox.classList.add('urgent');
    }
}

function initSensorAntiCheat() {
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && !EXAM_FINISHED) {
            laporkanPelanggaranKeServer('Berpindah Tab Browser / Minimize Jendela');
        }
    });

    window.addEventListener('blur', () => {
        if (!EXAM_FINISHED && !document.hidden) {
            laporkanPelanggaranKeServer('Kehilangan Fokus Jendela Ujian (Indikasi Alt+Tab)');
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement && !EXAM_FINISHED) {
            document.getElementById('fullscreenBlockerOverlay').classList.add('show');
            laporkanPelanggaranKeServer('Keluar dari Mode Layar Penuh (Exit Fullscreen / ESC)');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (
            e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) ||
            (e.ctrlKey && ['U', 'C', 'V', 'P', 'S'].includes(e.key.toUpperCase()))
        ) {
            e.preventDefault();
            laporkanPelanggaranKeServer(`Menekan Tombol Pintasan Terlarang (${e.key})`);
        }
    });
}

async function aktifkanKembaliFullscreen() {
    try {
        if (document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
        }
    } catch (e) {}
    document.getElementById('fullscreenBlockerOverlay').classList.remove('show');
}

async function laporkanPelanggaranKeServer(jenisPelanggaran) {
    if (EXAM_FINISHED) return;

    const now = Date.now();
    if (now - LAST_CHEAT_TIMESTAMP < 2500) return;
    LAST_CHEAT_TIMESTAMP = now;

    try {
        const res = await cbtFetch(`/api/murid/ujian/${ID_UJIAN_MURID}/lapor-pelanggaran`, {
            method: 'POST',
            body: JSON.stringify({
                jenis_pelanggaran: jenisPelanggaran,
                sisa_waktu_detik: SISA_DETIK
            })
        });
        const data = await res.json();

        if (data.jumlah_pelanggaran !== undefined) {
            document.getElementById('violationCounterText').textContent = `${data.jumlah_pelanggaran} / ${data.batas_toleransi}`;
        }

        if (data.status === 'auto_submitted' || data.status_ujian === 'auto_submit_pelanggaran') {
            EXAM_FINISHED = true;
            clearInterval(TIMER_INTERVAL);
            if (document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            }
            await cbtAlert(
                `<strong>Batas Maksimal Pelanggaran Tercapai (${data.jumlah_pelanggaran}/${data.batas_toleransi})</strong><br><br>${data.message}`,
                'Ujian Dihentikan Otomatis',
                'error'
            );
            window.location.href = '/murid/riwayat-nilai';
            return;
        }

        await cbtAlert(
            `<strong>Peringatan Keamanan Ujian (${data.jumlah_pelanggaran}/${data.batas_toleransi}):</strong><br>` +
            `Terdeteksi aktivitas: <strong>${jenisPelanggaran}</strong>.<br><br>` +
            `Apabila mencapai <strong>${data.batas_toleransi} kali pelanggaran</strong>, ujian akan dihentikan secara otomatis.`,
            'Peringatan Anti-Cheat',
            'error'
        );
    } catch (e) {}
}

async function konfirmasiSubmitMandiri() {
    const belumDijawab = DAFTAR_SOAL.filter(s => !s.pilihan_jawaban).length;
    const masihRagu = DAFTAR_SOAL.filter(s => s.status_ragu).length;

    let pesanTambahan = '';
    if (belumDijawab > 0 || masihRagu > 0) {
        pesanTambahan = `<br><br><span style="color:#E11D48; font-weight:700;">Catatan: Masih terdapat ${belumDijawab} soal belum dijawab dan ${masihRagu} soal ditandai Ragu-Ragu.</span>`;
    }

    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin mengakhiri ujian dan mengumpulkan seluruh lembar jawaban sekarang?${pesanTambahan}`,
        'Konfirmasi Kumpulkan Jawaban'
    );
    if (!yakin) return;

    EXAM_FINISHED = true;
    clearInterval(TIMER_INTERVAL);
    await eksekusiSubmitKeServer('selesai_mandiri', 'Lembar jawaban ujian Anda telah berhasil dikumpulkan.');
}

async function eksekusiSubmitKeServer(tipeSubmit, pesanSukses) {
    if (document.fullscreenElement && document.exitFullscreen) {
        document.exitFullscreen().catch(() => {});
    }

    const res = await cbtFetch(`/api/murid/ujian/${ID_UJIAN_MURID}/submit`, {
        method: 'POST',
        body: JSON.stringify({ tipe_submit: tipeSubmit })
    });
    const data = await res.json();

    let infoSkor = '';
    if (data.publikasi_nilai && data.hasil_nilai && data.hasil_nilai.nilai_akhir !== undefined) {
        infoSkor = `<br><br>Nilai Akhir: <strong class="font-mono">${data.hasil_nilai.nilai_akhir}</strong> (${data.hasil_nilai.status_kelulusan})`;
    }

    await cbtAlert(`${pesanSukses}${infoSkor}`, 'Ujian Selesai Dikumpulkan', 'success');
    window.location.href = '/murid/riwayat-nilai';
}