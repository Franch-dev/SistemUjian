/**
 * ==========================================================================
 * GURU/BANK-SOAL.JS — MULTI-FORM GOOGLE FORM BUILDER (BUAT FORM BARU TERPISAH)
 * ==========================================================================
 */

let SEMUA_PLOTTING_GURU = [];
let DAFTAR_FORMULIR_UJIAN = []; // Setiap item adalah 1 Formulir Ujian mandiri (SesiUjian)
let ACTIVE_PLOTTING = null;
let ACTIVE_ID_SESI = null; // null = sedang membuat Formulir Ujian Baru dari nol
let GF_QUESTIONS = [];

document.addEventListener('DOMContentLoaded', initBankSoalPage);

async function initBankSoalPage() {
    const res = await cbtFetch('/api/guru/plotting-ampuan');
    const json = await res.json();

    SEMUA_PLOTTING_GURU = json.data || [];
    DAFTAR_FORMULIR_UJIAN = json.formulir_list || [];

    const jurSet = new Set();
    SEMUA_PLOTTING_GURU.forEach(p => {
        if (p.kelas?.jurusan?.kode_jurusan) {
            jurSet.add(p.kelas.jurusan.kode_jurusan);
        }
    });

    const filJur = document.getElementById('filterPackageJurusan');
    const modJur = document.getElementById('modalSelectJurusan');
    filJur.innerHTML = '<option value="">Semua Jurusan Ampuan</option>';
    modJur.innerHTML = '<option value="ALL">Semua Jurusan Ampuan</option>';

    jurSet.forEach(kode => {
        filJur.innerHTML += `<option value="${kode}">Jurusan ${kode}</option>`;
        modJur.innerHTML += `<option value="${kode}">Jurusan ${kode}</option>`;
    });

    renderDaftarPaketFormulir();
}

/* ==========================================================================
   1. TAMPILAN UTAMA: DAFTAR KARTU FORMULIR UJIAN (BISA BANYAK FORM PER MAPEL)
   ========================================================================== */
function renderDaftarPaketFormulir() {
    const keyword = (document.getElementById('searchPackageInput')?.value || '').toLowerCase();
    const filterJur = document.getElementById('filterPackageJurusan')?.value || '';
    const container = document.getElementById('packageAccordionContainer');
    container.innerHTML = '';

    const filtered = DAFTAR_FORMULIR_UJIAN.filter(f => {
        const judul = (f.nama_formulir || '').toLowerCase();
        const mapel = (f.plotting?.mapel?.nama_mapel || '').toLowerCase();
        const kelas = (f.plotting?.kelas?.nama_kelas || '').toLowerCase();
        const kodeJur = f.plotting?.kelas?.jurusan?.kode_jurusan || '';
        return (judul.includes(keyword) || mapel.includes(keyword) || kelas.includes(keyword)) &&
               (!filterJur || kodeJur === filterJur);
    });

    if (filtered.length === 0) {
        container.innerHTML = `
            <div style="padding:36px 24px; text-align:center; color:#64748B; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:14px;">
                <h4 style="font-size:15px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Formulir Ujian</h4>
                <p style="font-size:13px; margin-bottom:14px;">Klik tombol <strong>+ Buat Formulir Ujian Baru</strong> di kanan atas untuk membuat paket soal ujian baru.</p>
                <button type="button" class="cbt-btn primary" onclick="bukaModalBuatFormBaru()">+ Buat Formulir Ujian Baru</button>
            </div>
        `;
        return;
    }

    filtered.forEach(f => {
        const p = f.plotting || {};
        const daftarSoal = f.daftar_soal || [];
        const totalBobot = f.total_bobot || 0;
        const kodeJur = p.kelas?.jurusan?.kode_jurusan || 'UMUM';

        let soalHtml = '';
        if (daftarSoal.length === 0) {
            soalHtml = `<p style="color:#64748B; font-size:12.5px; margin:0;">Formulir ini belum memiliki pertanyaan. Klik tombol <strong>Buka Editor Form</strong> untuk mengisi soal.</p>`;
        } else {
            daftarSoal.forEach((s, idx) => {
                soalHtml += `
                    <div class="question-preview-item">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:6px;">
                            <div style="font-weight:700; font-size:13.5px; color:#0F172A;">
                                ${idx + 1}. ${s.pertanyaan}
                            </div>
                            <div style="display:flex; gap:6px; flex-shrink:0;">
                                <span class="status-pill green font-mono">Kunci: ${s.kunci_jawaban}</span>
                                <span class="status-pill blue font-mono">Bobot: ${s.bobot_soal}</span>
                            </div>
                        </div>
                        ${s.url_gambar ? `<div style="margin:8px 0;"><img src="${s.url_gambar}" style="max-height:95px; border-radius:6px; border:1px solid #CBD5E1;"></div>` : ''}
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:8px; font-size:12px; color:#475569; margin-top:8px;">
                            <div><strong>A.</strong> ${formatTampilanOpsi(s.opsi_a)}</div>
                            <div><strong>B.</strong> ${formatTampilanOpsi(s.opsi_b)}</div>
                            <div><strong>C.</strong> ${formatTampilanOpsi(s.opsi_c)}</div>
                            <div><strong>D.</strong> ${formatTampilanOpsi(s.opsi_d)}</div>
                            <div><strong>E.</strong> ${formatTampilanOpsi(s.opsi_e)}</div>
                        </div>
                    </div>
                `;
            });
        }

        container.innerHTML += `
            <div class="gform-package-card">
                <div class="gform-package-header" onclick="toggleAccordionForm(${f.id_sesi})">
                    <div class="gform-package-title">
                        <h4>
                            <span>${f.nama_formulir}</span>
                            <span class="status-pill blue">${p.kelas?.nama_kelas || '-'} (${kodeJur})</span>
                            <span class="status-pill ${daftarSoal.length > 0 ? 'green' : 'amber'}">${daftarSoal.length} Butir Soal</span>
                        </h4>
                        <p>
                            Mata Pelajaran: <strong>${p.mapel?.nama_mapel || '-'}</strong> (<span class="font-mono">${p.mapel?.kode_mapel || '-'}</span>) &nbsp;•&nbsp;
                            KKM: <strong>${p.mapel?.nilai_kkm || 75}</strong> &nbsp;•&nbsp;
                            Total Bobot: <strong>${totalBobot} / 100 Poin</strong> &nbsp;•&nbsp;
                            <span style="color:#1D5DEC; font-weight:700;">(Klik untuk lihat daftar soal ▾)</span>
                        </p>
                    </div>
                    <div class="btn-group-row" onclick="event.stopPropagation();">
                        <button type="button" class="cbt-btn primary" onclick="bukaEditorFormulirExisting(${f.id_sesi})">
                            Buka / Edit Formulir
                        </button>
                        <button type="button" class="cbt-btn danger" onclick="hapusFormulirById(${f.id_plotting}, ${f.id_sesi}, '${(f.nama_formulir || '').replace(/'/g, "\\'")}')">
                            Hapus Formulir
                        </button>
                    </div>
                </div>

                <div id="formBody-${f.id_sesi}" class="gform-package-body">
                    ${soalHtml}
                </div>
            </div>
        `;
    });
}

function toggleAccordionForm(idSesi) {
    const bodyEl = document.getElementById(`formBody-${idSesi}`);
    if (bodyEl) bodyEl.classList.toggle('open');
}

function formatTampilanOpsi(rawText) {
    if (!rawText) return '-';
    const match = rawText.match(/\[IMG:(.*?)\]/);
    const cleanText = rawText.replace(/\[IMG:.*?\]/g, '').trim();
    if (match && match[1]) {
        return `${cleanText ? cleanText + '<br>' : ''}<img src="${match[1]}" style="max-height:55px; border-radius:4px; margin-top:3px; border:1px solid #CBD5E1;">`;
    }
    return cleanText || '-';
}

/* ==========================================================================
   2. MODAL BUAT FORMULIR UJIAN BARU (100% MULAI DARI KOSONG)
   ========================================================================== */
function bukaModalBuatFormBaru() {
    filterKelasDiModalTarget();
    openModal('modalPilihTargetSoal');
}

function filterKelasDiModalTarget() {
    const jur = document.getElementById('modalSelectJurusan').value;
    const selPlot = document.getElementById('modalSelectPlotting');
    selPlot.innerHTML = '';

    const list = SEMUA_PLOTTING_GURU.filter(p =>
        jur === 'ALL' || (p.kelas?.jurusan?.kode_jurusan === jur)
    );

    list.forEach(p => {
        const countForms = DAFTAR_FORMULIR_UJIAN.filter(f => f.id_plotting === p.id_plotting).length;
        selPlot.innerHTML += `<option value="${p.id_plotting}">${p.kelas?.nama_kelas} — ${p.mapel?.nama_mapel} (${countForms} Formulir Ada)</option>`;
    });

    isiJudulFormulirDefault();
}

function isiJudulFormulirDefault() {
    const idPlot = Number(document.getElementById('modalSelectPlotting').value);
    const plotObj = SEMUA_PLOTTING_GURU.find(x => x.id_plotting === idPlot);
    if (!plotObj) return;

    const existingFormsCount = DAFTAR_FORMULIR_UJIAN.filter(f => f.id_plotting === idPlot).length;
    const nomorFormBaru = existingFormsCount + 1;
    document.getElementById('modalJudulFormulirBaru').value =
        `Formulir Ujian #${nomorFormBaru} — ${plotObj.mapel?.nama_mapel || ''} (${plotObj.kelas?.nama_kelas || ''})`;
}

/**
 * Ketika Guru klik "Buat Formulir Ujian Baru ->" di Modal:
 * Langsung membuka Google Form BARU yang KOSONG (ACTIVE_ID_SESI = null)
 */
function mulaiBuatFormulirBaru(e) {
    e.preventDefault();
    const idPlotting = Number(document.getElementById('modalSelectPlotting').value);
    const judulBaru = document.getElementById('modalJudulFormulirBaru').value.trim();
    const jumlahSoalAwal = Math.max(1, Number(document.getElementById('modalTargetJumlahSoal').value) || 5);

    const plotObj = SEMUA_PLOTTING_GURU.find(x => x.id_plotting === idPlotting);
    if (!plotObj) return;

    closeModal('modalPilihTargetSoal');

    ACTIVE_PLOTTING = plotObj;
    ACTIVE_ID_SESI = null; // Menandakan ini adalah Formulir Ujian BARU!

    document.getElementById('gfBadgeKelasJurusan').textContent =
        `${plotObj.kelas?.nama_kelas || ''} • Jurusan ${plotObj.kelas?.jurusan?.kode_jurusan || 'UMUM'}`;
    document.getElementById('gfFormTitleInput').value = judulBaru;
    document.getElementById('gfFormSubtitleMapel').textContent =
        `Mata Pelajaran: ${plotObj.mapel?.nama_mapel || '-'} (${plotObj.mapel?.kode_mapel || '-'}) • Formulir Baru`;
    document.getElementById('btnHapusFormDiEditor').style.display = 'none';

    // Siapkan N kartu pertanyaan kosong dari 0 (tidak mencampur soal dari formulir lama!)
    GF_QUESTIONS = [];
    for (let i = 0; i < jumlahSoalAwal; i++) {
        GF_QUESTIONS.push(buatObjekSoalKosong());
    }
    hitungBobotOtomatis100(false);

    document.getElementById('mainPackageListView').style.display = 'none';
    document.getElementById('googleFormBuilderView').style.display = 'block';
    renderSeluruhKartuGoogleForm();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Membuka & Mengedit Formulir Ujian yang Sudah Ada Berdasarkan id_sesi
 */
function bukaEditorFormulirExisting(idSesi) {
    const formObj = DAFTAR_FORMULIR_UJIAN.find(f => f.id_sesi === idSesi);
    if (!formObj) return;

    ACTIVE_PLOTTING = formObj.plotting;
    ACTIVE_ID_SESI = formObj.id_sesi;

    document.getElementById('gfBadgeKelasJurusan').textContent =
        `${ACTIVE_PLOTTING?.kelas?.nama_kelas || ''} • Jurusan ${ACTIVE_PLOTTING?.kelas?.jurusan?.kode_jurusan || 'UMUM'}`;
    document.getElementById('gfFormTitleInput').value = formObj.nama_formulir;
    document.getElementById('gfFormSubtitleMapel').textContent =
        `Mata Pelajaran: ${ACTIVE_PLOTTING?.mapel?.nama_mapel || '-'} (${ACTIVE_PLOTTING?.mapel?.kode_mapel || '-'})`;
    document.getElementById('btnHapusFormDiEditor').style.display = 'inline-flex';

    const existingSoal = formObj.daftar_soal || [];
    if (existingSoal.length > 0) {
        GF_QUESTIONS = existingSoal.map(s => ({
            id_soal: s.id_soal,
            pertanyaan: s.pertanyaan,
            path_gambar: s.path_gambar,
            url_gambar: s.url_gambar,
            opsi_a: extractTextOnly(s.opsi_a),
            opsi_a_img: extractImgUrl(s.opsi_a),
            opsi_b: extractTextOnly(s.opsi_b),
            opsi_b_img: extractImgUrl(s.opsi_b),
            opsi_c: extractTextOnly(s.opsi_c),
            opsi_c_img: extractImgUrl(s.opsi_c),
            opsi_d: extractTextOnly(s.opsi_d),
            opsi_d_img: extractImgUrl(s.opsi_d),
            opsi_e: extractTextOnly(s.opsi_e),
            opsi_e_img: extractImgUrl(s.opsi_e),
            kunci_jawaban: s.kunci_jawaban || 'A',
            bobot_soal: s.bobot_soal || 20
        }));
    } else {
        GF_QUESTIONS = [buatObjekSoalKosong()];
        hitungBobotOtomatis100(false);
    }

    document.getElementById('mainPackageListView').style.display = 'none';
    document.getElementById('googleFormBuilderView').style.display = 'block';
    renderSeluruhKartuGoogleForm();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function buatObjekSoalKosong() {
    return {
        id_soal: null,
        pertanyaan: '',
        path_gambar: null,
        url_gambar: null,
        opsi_a: '', opsi_a_img: null,
        opsi_b: '', opsi_b_img: null,
        opsi_c: '', opsi_c_img: null,
        opsi_d: '', opsi_d_img: null,
        opsi_e: '', opsi_e_img: null,
        kunci_jawaban: 'A',
        bobot_soal: 20
    };
}

function extractTextOnly(raw) {
    return (raw || '').replace(/\[IMG:.*?\]/g, '').trim();
}

function extractImgUrl(raw) {
    const m = (raw || '').match(/\[IMG:(.*?)\]/);
    return m ? m[1] : null;
}

/* ==========================================================================
   3. RENDER KARTU PERTANYAAN GOOGLE FORM BUILDER
   ========================================================================== */
function renderSeluruhKartuGoogleForm() {
    const container = document.getElementById('gfQuestionsListContainer');
    container.innerHTML = '';

    GF_QUESTIONS.forEach((q, idx) => {
        const opsiKeys = ['a', 'b', 'c', 'd', 'e'];
        let opsiRowsHtml = '';

        opsiKeys.forEach(k => {
            const upperK = k.toUpperCase();
            const textVal = (q[`opsi_${k}`] || '').replace(/"/g, '&quot;');
            const imgUrl = q[`opsi_${k}_img`];
            const isChecked = q.kunci_jawaban === upperK ? 'checked' : '';

            opsiRowsHtml += `
                <div>
                    <div class="gform-opt-item">
                        <input type="radio" name="kunci_soal_${idx}" class="gform-opt-radio"
                            ${isChecked} onchange="updateStateSoal(${idx}, 'kunci_jawaban', '${upperK}')"
                            title="Klik untuk menetapkan Opsi ${upperK} sebagai Kunci Jawaban">
                        <span class="gform-opt-letter">${upperK}.</span>
                        <input type="text" class="gform-opt-text-input"
                            placeholder="Opsi ${upperK} (Ketik teks atau unggah gambar di kanan)"
                            value="${textVal}"
                            oninput="updateStateSoal(${idx}, 'opsi_${k}', this.value)">

                        <label class="gform-icon-upload-btn" title="Sisipkan Gambar pada Opsi ${upperK}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <input type="file" hidden accept="image/*" onchange="uploadGambarOpsi(${idx}, '${k}', this)">
                        </label>
                    </div>

                    ${imgUrl ? `
                        <div class="gform-opt-img-box">
                            <img src="${imgUrl}" alt="Gambar Opsi ${upperK}">
                            <button type="button" class="cbt-btn danger" style="padding:4px 10px; font-size:11px;"
                                onclick="hapusGambarOpsi(${idx}, '${k}')">Hapus Gambar Opsi ${upperK}</button>
                        </div>
                    ` : ''}
                </div>
            `;
        });

        container.innerHTML += `
            <div class="gform-question-card" id="gfCard-${idx}">
                <div class="gform-drag-dots">
                    <span>Pertanyaan #${idx + 1} dari ${GF_QUESTIONS.length}</span>
                </div>

                <div class="gform-question-header-grid">
                    <div>
                        <div class="gform-q-box-wrapper">
                            <textarea id="gfTextarea-${idx}" class="gform-q-textarea"
                                placeholder="Tuliskan Pertanyaan Soal #${idx + 1} di sini..."
                                oninput="updateStateSoal(${idx}, 'pertanyaan', this.value)">${q.pertanyaan}</textarea>
                        </div>
                        <div class="gform-format-bar">
                            <button type="button" class="gform-fmt-btn" onclick="sisipkanFormatTeks(${idx}, 'b')" title="Tebal (Bold)">B</button>
                            <button type="button" class="gform-fmt-btn" style="font-style:italic;" onclick="sisipkanFormatTeks(${idx}, 'i')" title="Miring (Italic)">I</button>
                            <button type="button" class="gform-fmt-btn" style="text-decoration:underline;" onclick="sisipkanFormatTeks(${idx}, 'u')" title="Garis Bawah">U</button>
                            <span style="font-size:11.5px; color:#6B7280; margin-left:8px;">Pilih bulatan opsi di bawah untuk menetapkan Kunci Jawaban</span>
                        </div>
                    </div>

                    <label class="gform-icon-upload-btn" title="Sisipkan Gambar pada Pertanyaan">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <input type="file" hidden accept="image/*" onchange="uploadGambarPertanyaan(${idx}, this)">
                    </label>

                    <div class="gform-type-selector">
                        <span class="gform-type-selector-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4" fill="#4F46E5"/>
                            </svg>
                            <span>Pilihan ganda</span>
                        </span>
                    </div>
                </div>

                ${q.url_gambar ? `
                    <div class="gform-opt-img-box" style="margin-left:0; margin-top:12px;">
                        <img src="${q.url_gambar}" style="max-height:130px;">
                        <button type="button" class="cbt-btn danger" onclick="hapusGambarPertanyaan(${idx})">Hapus Gambar Pertanyaan</button>
                    </div>
                ` : ''}

                <div class="gform-options-list">
                    ${opsiRowsHtml}
                </div>

                <div class="gform-card-bottom-bar">
                    <div class="gform-key-weight-group">
                        <span class="status-pill green font-mono" id="badgeKunci-${idx}" style="padding:6px 12px; font-size:12px;">
                            Kunci Jawaban: Opsi ${q.kunci_jawaban}
                        </span>

                        <div style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#374151;">
                            <span>Bobot Poin:</span>
                            <input type="number" class="gform-weight-input font-mono"
                                value="${q.bobot_soal}" min="1" max="100"
                                oninput="updateStateSoal(${idx}, 'bobot_soal', Number(this.value)); updateRingkasanBobotBawah();">
                            <span style="color:#6B7280; font-weight:500;">poin</span>
                        </div>
                    </div>

                    <div class="gform-bottom-actions">
                        <button type="button" class="gform-icon-action-btn" onclick="duplikatKartuSoal(${idx})" title="Duplikat Pertanyaan Ini">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>

                        <button type="button" class="gform-icon-action-btn delete" onclick="hapusKartuSoal(${idx})" title="Hapus Pertanyaan Ini">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    updateRingkasanBobotBawah();
}

function updateStateSoal(index, field, value) {
    if (!GF_QUESTIONS[index]) return;
    GF_QUESTIONS[index][field] = value;
    if (field === 'kunci_jawaban') {
        const badge = document.getElementById(`badgeKunci-${index}`);
        if (badge) badge.textContent = `Kunci Jawaban: Opsi ${value}`;
    }
}

function sisipkanFormatTeks(index, tag) {
    const ta = document.getElementById(`gfTextarea-${index}`);
    if (!ta) return;
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = ta.value.substring(start, end) || 'teks';
    const replacement = `<${tag}>${selected}</${tag}>`;
    ta.value = ta.value.substring(0, start) + replacement + ta.value.substring(end);
    GF_QUESTIONS[index].pertanyaan = ta.value;
}

function hitungBobotOtomatis100(tampilkanPesan = false) {
    const n = GF_QUESTIONS.length;
    if (n === 0) return;

    const baseWeight = Math.floor(100 / n);
    let remainder = 100 - (baseWeight * n);

    GF_QUESTIONS.forEach((q) => {
        q.bobot_soal = baseWeight + (remainder > 0 ? 1 : 0);
        if (remainder > 0) remainder--;
    });

    renderSeluruhKartuGoogleForm();
    if (tampilkanPesan) {
        cbtAlert(`Bobot nilai untuk <strong>${n} butir soal</strong> telah dibagi rata secara otomatis dengan total <strong>100 Poin</strong>.`, 'Bobot Otomatis 100');
    }
}

function updateRingkasanBobotBawah() {
    const totalSoal = GF_QUESTIONS.length;
    const totalBobot = GF_QUESTIONS.reduce((acc, q) => acc + Number(q.bobot_soal || 0), 0);

    document.getElementById('gfTotalSoalCount').textContent = totalSoal;
    const bobotEl = document.getElementById('gfTotalBobotCount');
    bobotEl.textContent = totalBobot;
    bobotEl.style.color = totalBobot === 100 ? '#10B981' : '#E11D48';
}

function tambahKartuPertanyaanBaru() {
    GF_QUESTIONS.push(buatObjekSoalKosong());
    const newIndex = GF_QUESTIONS.length - 1;
    hitungBobotOtomatis100(false);

    setTimeout(() => {
        const ta = document.getElementById(`gfTextarea-${newIndex}`);
        if (ta) {
            ta.scrollIntoView({ behavior: 'smooth', block: 'center' });
            ta.focus();
        }
    }, 100);
}

function duplikatKartuSoal(index) {
    const copy = JSON.parse(JSON.stringify(GF_QUESTIONS[index]));
    copy.id_soal = null;
    GF_QUESTIONS.splice(index + 1, 0, copy);
    hitungBobotOtomatis100(false);
}

async function hapusKartuSoal(index) {
    if (GF_QUESTIONS.length <= 1) {
        await cbtAlert('Minimal harus terdapat 1 kartu pertanyaan di dalam formulir.', 'Tidak Dapat Dihapus', 'error');
        return;
    }
    const yakin = await cbtConfirm(`Hapus kartu <strong>Pertanyaan #${index + 1}</strong>? Bobot soal lainnya akan otomatis disesuaikan kembali ke total 100.`, 'Hapus Kartu Soal', 'danger');
    if (!yakin) return;

    GF_QUESTIONS.splice(index, 1);
    hitungBobotOtomatis100(false);
}

async function uploadGambarPertanyaan(index, inputEl) {
    if (!inputEl.files.length) return;
    const fd = new FormData();
    fd.append('file', inputEl.files[0]);

    const res = await cbtFetch('/api/guru/bank-soal/upload-media', { method: 'POST', body: fd });
    const data = await res.json();
    inputEl.value = '';

    if (res.ok && data.url) {
        GF_QUESTIONS[index].path_gambar = data.path;
        GF_QUESTIONS[index].url_gambar = data.url;
        renderSeluruhKartuGoogleForm();
    } else {
        await cbtAlert('Gagal mengunggah gambar pertanyaan.', 'Upload Gagal', 'error');
    }
}

function hapusGambarPertanyaan(index) {
    GF_QUESTIONS[index].path_gambar = null;
    GF_QUESTIONS[index].url_gambar = null;
    renderSeluruhKartuGoogleForm();
}

async function uploadGambarOpsi(index, keyLower, inputEl) {
    if (!inputEl.files.length) return;
    const fd = new FormData();
    fd.append('file', inputEl.files[0]);

    const res = await cbtFetch('/api/guru/bank-soal/upload-media', { method: 'POST', body: fd });
    const data = await res.json();
    inputEl.value = '';

    if (res.ok && data.url) {
        GF_QUESTIONS[index][`opsi_${keyLower}_img`] = data.url;
        if (!GF_QUESTIONS[index][`opsi_${keyLower}`]) {
            GF_QUESTIONS[index][`opsi_${keyLower}`] = `Gambar Opsi ${keyLower.toUpperCase()}`;
        }
        renderSeluruhKartuGoogleForm();
    } else {
        await cbtAlert('Gagal mengunggah gambar opsi jawaban.', 'Upload Gagal', 'error');
    }
}

function hapusGambarOpsi(index, keyLower) {
    GF_QUESTIONS[index][`opsi_${keyLower}_img`] = null;
    renderSeluruhKartuGoogleForm();
}

/* ==========================================================================
   4. SIMPAN FORMULIR UJIAN (BARU MAUPUN EDIT) & HAPUS FORMULIR UJIAN
   ========================================================================== */
async function simpanSeluruhFormSoal() {
    const namaFormulir = document.getElementById('gfFormTitleInput').value.trim();
    if (!namaFormulir) {
        await cbtAlert('Judul Formulir Ujian tidak boleh kosong.', 'Judul Kosong', 'error');
        return;
    }

    for (let i = 0; i < GF_QUESTIONS.length; i++) {
        const q = GF_QUESTIONS[i];
        if (!q.pertanyaan.trim()) {
            await cbtAlert(`Pertanyaan pada <strong>Kartu Soal #${i + 1}</strong> masih kosong. Silakan lengkapi terlebih dahulu.`, 'Pertanyaan Kosong', 'error');
            return;
        }
    }

    const totalBobot = GF_QUESTIONS.reduce((acc, q) => acc + Number(q.bobot_soal || 0), 0);
    const yakin = await cbtConfirm(
        `Simpan formulir ujian <strong>${namaFormulir}</strong> (${GF_QUESTIONS.length} butir soal, Total Bobot: ${totalBobot} Poin)?`,
        'Konfirmasi Simpan Formulir Ujian'
    );
    if (!yakin) return;

    const itemsPayload = GF_QUESTIONS.map(q => {
        const gabungOpsi = (teks, imgUrl, label) => {
            const t = (teks || '').trim() || (imgUrl ? `Gambar ${label}` : '-');
            return imgUrl ? `${t} [IMG:${imgUrl}]` : t;
        };

        return {
            id_soal: q.id_soal,
            pertanyaan: q.pertanyaan.trim(),
            path_gambar: q.path_gambar,
            opsi_a: gabungOpsi(q.opsi_a, q.opsi_a_img, 'A'),
            opsi_b: gabungOpsi(q.opsi_b, q.opsi_b_img, 'B'),
            opsi_c: gabungOpsi(q.opsi_c, q.opsi_c_img, 'C'),
            opsi_d: gabungOpsi(q.opsi_d, q.opsi_d_img, 'D'),
            opsi_e: gabungOpsi(q.opsi_e, q.opsi_e_img, 'E'),
            kunci_jawaban: q.kunci_jawaban,
            bobot_soal: Number(q.bobot_soal || 1)
        };
    });

    const res = await cbtFetch(`/api/guru/plotting/${ACTIVE_PLOTTING.id_plotting}/bank-soal/bulk-save`, {
        method: 'POST',
        body: JSON.stringify({
            id_sesi: ACTIVE_ID_SESI,
            nama_formulir: namaFormulir,
            items: itemsPayload
        })
    });
    const data = await res.json();

    if (res.ok) {
        await cbtAlert(data.message, 'Formulir Ujian Tersimpan!', 'success');
        document.getElementById('googleFormBuilderView').style.display = 'none';
        document.getElementById('mainPackageListView').style.display = 'block';
        await initBankSoalPage();
    } else {
        await cbtAlert(data.message || 'Gagal menyimpan formulir soal.', 'Gagal Menyimpan', 'error');
    }
}

async function batalEditSoalGoogleForm() {
    const yakin = await cbtConfirm(
        'Apakah Anda yakin ingin keluar dari Editor? Perubahan yang belum disimpan akan dibuang.',
        'Konfirmasi Batal',
        'danger'
    );
    if (!yakin) return;

    document.getElementById('googleFormBuilderView').style.display = 'none';
    document.getElementById('mainPackageListView').style.display = 'block';
}

async function hapusFormulirAktif() {
    if (!ACTIVE_PLOTTING || !ACTIVE_ID_SESI) return;
    const judul = document.getElementById('gfFormTitleInput').value || 'Formulir Ini';
    await hapusFormulirById(ACTIVE_PLOTTING.id_plotting, ACTIVE_ID_SESI, judul);
}

async function hapusFormulirById(idPlotting, idSesi, namaFormulir) {
    const yakin = await cbtConfirm(
        `Apakah Anda yakin ingin menghapus <strong>${namaFormulir}</strong> beserta seluruh butir soal di dalamnya secara permanen?`,
        'Konfirmasi Hapus Formulir Ujian',
        'danger'
    );
    if (!yakin) return;

    const res = await cbtFetch(`/api/guru/plotting/${idPlotting}/bank-soal/clear-all?id_sesi=${idSesi}`, {
        method: 'DELETE'
    });
    const data = await res.json();

    await cbtAlert(data.message, res.ok ? 'Formulir Dihapus' : 'Gagal', res.ok ? 'success' : 'error');
    if (res.ok) {
        document.getElementById('googleFormBuilderView').style.display = 'none';
        document.getElementById('mainPackageListView').style.display = 'block';
        await initBankSoalPage();
    }
}