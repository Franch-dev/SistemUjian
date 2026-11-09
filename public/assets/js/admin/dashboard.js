/**
 * ==========================================================================
 * ADMIN-DASHBOARD.JS — 100% DATA DINAMIS DARI DATABASE MYSQL
 * ==========================================================================
 */

let DB_CHART_DATA = {
    labels: ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
    hari_ini: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    kemarin: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
};

document.addEventListener('DOMContentLoaded', muatDashboardRealTime);

async function muatDashboardRealTime() {
    try {
        const res = await cbtFetch('/api/admin/dashboard');
        const json = await res.json();

        // 1. Update Waktu & Banner
        document.getElementById('lastUpdatedTime').textContent = `Terakhir diperbarui: ${json.waktu_server || ''}`;
        const userRaw = localStorage.getItem('cbt_user');
        if (userRaw) {
            const u = JSON.parse(userRaw);
            document.getElementById('welcomeAdminName').textContent = u.username || 'Administrator';
        }

        // 2. Update 4 Kartu KPI
        if (json.kpi) {
            document.getElementById('kpiTotalMurid').textContent = json.kpi.total_murid;
            document.getElementById('kpiTotalGuru').textContent = json.kpi.total_guru;
            document.getElementById('kpiTotalPlottingSub').textContent = `${json.kpi.total_plotting} Kelas ter-plotting`;
            document.getElementById('kpiSesiAktif').textContent = json.kpi.sesi_ujian_aktif;
            document.getElementById('kpiMengerjakanSub').textContent = `${json.kpi.murid_mengerjakan} Peserta sedang mengerjakan`;
            document.getElementById('kpiAntiCheat').textContent = json.kpi.total_log_pelanggaran;

            document.getElementById('bannerSesiStatusText').textContent = json.kpi.sesi_ujian_aktif > 0
                ? `${json.kpi.sesi_ujian_aktif} Sesi ujian sedang terbuka`
                : 'Tidak ada sesi ujian terbuka';
        }

        // 3. Update Donut Chart Status Peserta dari Database
        if (json.status_peserta) {
            const sp = json.status_peserta;
            document.getElementById('statSelesaiCount').textContent = sp.selesai;
            document.getElementById('statBerlangsungCount').textContent = sp.berlangsung;
            document.getElementById('statBelumCount').textContent = sp.belum_mulai;

            document.getElementById('statSelesaiPct').textContent = `${sp.pct_selesai}%`;
            document.getElementById('statBerlangsungPct').textContent = `${sp.pct_berlangsung}%`;
            document.getElementById('statBelumPct').textContent = `${sp.pct_belum}%`;
            document.getElementById('donutCenterPct').textContent = `${sp.pct_selesai}%`;

            // Keliling lingkaran r=52 adalah 2 * PI * 52 = 326.72 (~327)
            const circumference = 327;
            const lenSelesai = Math.round((sp.pct_selesai / 100) * circumference);
            const lenBerlangsung = Math.round((sp.pct_berlangsung / 100) * circumference);
            const lenBelum = Math.max(0, circumference - lenSelesai - lenBerlangsung);

            document.getElementById('donutArcSelesai').setAttribute('stroke-dasharray', `${lenSelesai} ${circumference}`);
            document.getElementById('donutArcSelesai').setAttribute('stroke-dashoffset', '0');

            document.getElementById('donutArcBerlangsung').setAttribute('stroke-dasharray', `${lenBerlangsung} ${circumference}`);
            document.getElementById('donutArcBerlangsung').setAttribute('stroke-dashoffset', `-${lenSelesai}`);

            document.getElementById('donutArcBelum').setAttribute('stroke-dasharray', `${lenBelum} ${circumference}`);
            document.getElementById('donutArcBelum').setAttribute('stroke-dashoffset', `-${lenSelesai + lenBerlangsung}`);
        }

        // 4. Update Distribusi Peserta per Jurusan dari Database
        const jurBox = document.getElementById('jurusanDistributionContainer');
        jurBox.innerHTML = '';
        const colors = ['blue', 'green', 'purple', 'amber'];
        (json.distribusi_jurusan || []).forEach((j, idx) => {
            const color = colors[idx % colors.length];
            jurBox.innerHTML += `
                <div>
                    <div class="jurusan-item-top">
                        <span class="legend-name">
                            <span class="dot-indicator ${color}"></span>
                            <strong>${j.kode_jurusan}</strong> (${j.nama_jurusan})
                        </span>
                        <strong>${j.sudah_ujian} / ${j.total_murid} Murid <span style="color:#94A3B8; font-weight:500;">${j.persentase}%</span></strong>
                    </div>
                    <div class="jurusan-bar-track">
                        <div class="jurusan-bar-fill ${color}" style="width:${j.persentase}%"></div>
                    </div>
                </div>
            `;
        });

        // 5. Update Aktivitas Terbaru dari Database
        const actBox = document.getElementById('recentActivityList');
        actBox.innerHTML = '';
        const listAct = json.aktivitas_terbaru || [];
        if (listAct.length === 0) {
            actBox.innerHTML = `<p style="font-size:12px; color:#64748B;">Belum ada aktivitas ujian atau pelanggaran tercatat.</p>`;
        } else {
            listAct.forEach(a => {
                actBox.innerHTML += `
                    <div class="activity-feed-row">
                        <div class="activity-left">
                            <span class="dot-indicator ${a.warna}"></span>
                            <div>
                                <h4>${a.judul}</h4>
                                <p>${a.subjudul}</p>
                            </div>
                        </div>
                        <span class="activity-time font-mono">${a.waktu}</span>
                    </div>
                `;
            });
        }

        // 6. Render Grafik Aktivitas Ujian Per Jam dari Database
        if (json.grafik_per_jam) {
            DB_CHART_DATA = json.grafik_per_jam;
            renderChart('db_real');
        }
    } catch (e) {}
}

function switchChartPeriod(mode, btnEl) {
    document.querySelectorAll('.time-filter-group .filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    renderChart(mode);
}

function renderChart(mode) {
    const labels = DB_CHART_DATA.labels;
    let day1 = DB_CHART_DATA.hari_ini;
    let day2 = DB_CHART_DATA.kemarin;

    if (mode === 'simulasi_ramai') {
        day1 = [12, 45, 78, 95, 130, 88, 64, 40, 55, 82];
        day2 = [8, 32, 60, 75, 105, 70, 50, 30, 42, 65];
    }

    const maxVal = Math.max(10, ...day1, ...day2);
    const startX = 55;
    const endX = 565;
    const baseY = 185;
    const chartHeight = 160;
    const stepX = (endX - startX) / (labels.length - 1);

    // Update Y-Axis Labels sesuai nilai maksimum
    const yLabelsG = document.getElementById('chartYLabels');
    yLabelsG.innerHTML = [1, 0.8, 0.6, 0.4, 0.2, 0].map((ratio, idx) => {
        const yPos = 24 + idx * 33;
        return `<text x="30" y="${yPos}">${Math.round(maxVal * ratio)}</text>`;
    }).join('');

    const calcY = (val) => Math.round(baseY - (val / maxVal) * chartHeight);

    const pts1 = day1.map((v, i) => ({ x: Math.round(startX + i * stepX), y: calcY(v), val: v, label: labels[i] }));
    const pts2 = day2.map((v, i) => ({ x: Math.round(startX + i * stepX), y: calcY(v), val: v, label: labels[i] }));

    const poly1 = pts1.map(p => `${p.x},${p.y}`).join(' ');
    const poly2 = pts2.map(p => `${p.x},${p.y}`).join(' ');
    const area1 = `M${pts1[0].x},${pts1[0].y} ` + pts1.slice(1).map(p => `L${p.x},${p.y}`).join(' ') + ` L${pts1[pts1.length - 1].x},${baseY} L${pts1[0].x},${baseY} Z`;

    document.getElementById('chartLineDay1').setAttribute('points', poly1);
    document.getElementById('chartLineDay2').setAttribute('points', poly2);
    document.getElementById('chartAreaDay1').setAttribute('d', area1);

    document.getElementById('chartPointsDay1').innerHTML = pts1.map(p =>
        `<circle cx="${p.x}" cy="${p.y}" r="4" style="cursor:pointer;" onclick="cbtAlert('Pukul <strong>${p.label}</strong>:<br>• Hari Pertama: <strong>${p.val} Peserta</strong>', 'Detail Aktivitas Jam ${p.label}')"></circle>`
    ).join('');

    document.getElementById('chartPointsDay2').innerHTML = pts2.map(p =>
        `<circle cx="${p.x}" cy="${p.y}" r="3.2"></circle>`
    ).join('');

    document.getElementById('chartXLabels').innerHTML = pts1.map(p => `<text x="${p.x}" y="204">${p.label}</text>`).join('');
}