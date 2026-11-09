<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ProfilSekolah;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\PlottingMapel;
use App\Models\SesiUjian;
use App\Models\BankSoal;

class MasterAkademikController extends Controller
{
    // =========================================================================
    // 1. MASTER JURUSAN
    // =========================================================================
    public function indexJurusan(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => MasterJurusan::withCount('kelas')->get()
        ]);
    }

    public function storeJurusan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:15|unique:master_jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string|max:100',
        ]);

        $validated['kode_jurusan'] = strtoupper(trim($validated['kode_jurusan']));
        $jurusan = MasterJurusan::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurusan berhasil ditambahkan.',
            'data'    => $jurusan
        ], 201);
    }

    public function updateJurusan(Request $request, int $id): JsonResponse
    {
        $jurusan = MasterJurusan::findOrFail($id);

        $validated = $request->validate([
            'kode_jurusan' => "required|string|max:15|unique:master_jurusan,kode_jurusan,{$id},id_jurusan",
            'nama_jurusan' => 'required|string|max:100',
        ]);

        $validated['kode_jurusan'] = strtoupper(trim($validated['kode_jurusan']));
        $jurusan->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurusan berhasil diperbarui.',
            'data'    => $jurusan
        ]);
    }

    public function destroyJurusan(int $id): JsonResponse
    {
        MasterJurusan::findOrFail($id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurusan berhasil dihapus.'
        ]);
    }

    // =========================================================================
    // 2. MASTER KELAS
    // =========================================================================
    public function indexKelas(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => MasterKelas::with('jurusan')->withCount('murid')->get()
        ]);
    }

    public function storeKelas(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_jurusan'  => 'required|exists:master_jurusan,id_jurusan',
            'tingkat'     => 'required|in:X,XI,XII',
            'kode_rombel' => 'required|string|max:10',
            'nama_kelas'  => 'nullable|string|max:30|unique:master_kelas,nama_kelas',
        ]);

        $jurusan = MasterJurusan::findOrFail($validated['id_jurusan']);
        $validated['nama_kelas'] = $validated['nama_kelas']
            ?? trim("{$validated['tingkat']} {$jurusan->kode_jurusan} {$validated['kode_rombel']}");

        $kelas = MasterKelas::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kelas berhasil ditambahkan.',
            'data'    => $kelas->load('jurusan')
        ], 201);
    }

    public function updateKelas(Request $request, int $id): JsonResponse
    {
        $kelas = MasterKelas::findOrFail($id);

        $validated = $request->validate([
            'id_jurusan'  => 'required|exists:master_jurusan,id_jurusan',
            'tingkat'     => 'required|in:X,XI,XII',
            'kode_rombel' => 'required|string|max:10',
            'nama_kelas'  => "nullable|string|max:30|unique:master_kelas,nama_kelas,{$id},id_kelas",
        ]);

        $jurusan = MasterJurusan::findOrFail($validated['id_jurusan']);
        $validated['nama_kelas'] = $validated['nama_kelas']
            ?? trim("{$validated['tingkat']} {$jurusan->kode_jurusan} {$validated['kode_rombel']}");

        $kelas->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kelas berhasil diperbarui.',
            'data'    => $kelas->load('jurusan')
        ]);
    }

    public function destroyKelas(int $id): JsonResponse
    {
        MasterKelas::findOrFail($id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kelas berhasil dihapus.'
        ]);
    }

    // =========================================================================
    // 3. MASTER MATA PELAJARAN & NILAI KKM
    // =========================================================================
    public function indexMapel(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => MasterMapel::all()
        ]);
    }

    public function storeMapel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_mapel' => 'nullable|string|max:30|unique:master_mapel,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
            'nilai_kkm'  => 'required|integer|min:0|max:100',
        ]);

        if (empty($validated['kode_mapel'])) {
            $validated['kode_mapel'] = 'UMUM-ALL-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $validated['nama_mapel']), 0, 4)) . rand(10, 99);
        }

        $mapel = MasterMapel::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'data'    => $mapel
        ], 201);
    }

    public function updateMapel(Request $request, int $id): JsonResponse
    {
        $mapel = MasterMapel::findOrFail($id);

        $validated = $request->validate([
            'kode_mapel' => "required|string|max:30|unique:master_mapel,kode_mapel,{$id},id_mapel",
            'nama_mapel' => 'required|string|max:100',
            'nilai_kkm'  => 'required|integer|min:0|max:100',
        ]);

        $mapel->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data'    => $mapel
        ]);
    }

    public function destroyMapel(int $id): JsonResponse
    {
        MasterMapel::findOrFail($id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Mata pelajaran berhasil dihapus.'
        ]);
    }

    // =========================================================================
    // 4. PLOTTING GURU PENGAMPU (DENGAN VALIDASI ANTI-BENTROK)
    // =========================================================================
    public function indexPlotting(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => PlottingMapel::with(['guru', 'mapel', 'kelas.jurusan'])->withCount('bankSoal')->get()
        ]);
    }

    public function storePlotting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_guru'  => 'required|exists:guru,id_guru',
            'id_mapel' => 'required|exists:master_mapel,id_mapel',
            'id_kelas' => 'required|exists:master_kelas,id_kelas',
        ]);

        $exists = PlottingMapel::where('id_mapel', $validated['id_mapel'])
            ->where('id_kelas', $validated['id_kelas'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Konflik Plotting: Mata pelajaran ini pada kelas tersebut sudah memiliki Guru Pengampu.',
            ], 422);
        }

        $plotting = PlottingMapel::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Plotting Guru Pengampu berhasil disimpan.',
            'data'    => $plotting->load(['guru', 'mapel', 'kelas.jurusan']),
        ], 201);
    }

    public function destroyPlotting(int $id): JsonResponse
    {
        PlottingMapel::findOrFail($id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Plotting berhasil dihapus.'
        ]);
    }

    // =========================================================================
    // 5. KONFIGURASI & KONTROL SESI UJIAN (OTOMATIS BUKA/TUTUP SESUAI JAM WIB)
    // =========================================================================

    /**
     * Sinkronisasi Otomatis Status Sesi Ujian Berdasarkan Tanggal & Jam WIB (Asia/Jakarta)
     * - Otomatis TERBUKA saat memasuki jam_mulai s/d jam_selesai
     * - Otomatis TERTUTUP & Auto-Submit saat melewati jam_selesai (kecuali FORCE_OPEN_ADMIN)
     */
    private function sinkronisasiJadwalWib(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $semuaSesi = SesiUjian::with('ujianMurid')->get();

        foreach ($semuaSesi as $sesi) {
            $tglStr = Carbon::parse($sesi->tanggal_ujian)->format('Y-m-d');
            $jamMulaiStr = substr((string) $sesi->jam_mulai, 0, 5) . ':00';
            $jamSelesaiStr = substr((string) $sesi->jam_selesai, 0, 5) . ':59';

            $waktuMulai = Carbon::parse("{$tglStr} {$jamMulaiStr}", 'Asia/Jakarta');
            $waktuSelesai = Carbon::parse("{$tglStr} {$jamSelesaiStr}", 'Asia/Jakarta');

            $isWithinSchedule = $now->betweenIncluded($waktuMulai, $waktuSelesai);
            $isPastSchedule = $now->gt($waktuSelesai);
            $catatan = $sesi->catatan_berita_acara ?? '';

            if ($isWithinSchedule) {
                // Jika sudah masuk jam ujian (misal 07:00 - 08:00), otomatis buka kecuali ditutup paksa oleh Admin
                if ($sesi->status_sesi !== 'terbuka' && $catatan !== 'FORCE_CLOSE_ADMIN') {
                    $sesi->update([
                        'status_sesi'        => 'terbuka',
                        'visibilitas_jadwal' => 'tampil',
                    ]);
                }
            } else {
                // Jika di luar jam ujian (belum mulai atau sudah lewat jam selesai)
                // Otomatis tutup sesi kecuali Admin sengaja melakukan FORCE_OPEN_ADMIN (Override)
                if ($sesi->status_sesi === 'terbuka' && $catatan !== 'FORCE_OPEN_ADMIN') {
                    $sesi->update(['status_sesi' => 'tertutup']);
                }

                // Jika waktu sudah lewat jam selesai dan tidak di-override Admin, auto-submit murid yang masih mengerjakan
                if ($isPastSchedule && $catatan !== 'FORCE_OPEN_ADMIN') {
                    foreach ($sesi->ujianMurid as $ujian) {
                        if ($ujian->status_ujian === 'mengerjakan') {
                            $ujian->update([
                                'status_ujian'     => 'auto_submit_waktu',
                                'sisa_waktu_detik' => 0,
                                'fullscreen_lock'  => false,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function indexSesiUjian(): JsonResponse
    {
        $this->sinkronisasiJadwalWib();

        $data = SesiUjian::with([
            'plotting' => fn($q) => $q->withCount('bankSoal'),
            'plotting.guru',
            'plotting.mapel',
            'plotting.kelas.jurusan',
        ])
        ->orderBy('tanggal_ujian', 'asc')
        ->orderBy('jam_mulai', 'asc')
        ->get();

        // Hitung jumlah soal spesifik per Formulir Ujian (id_sesi)
        $hasIdSesi = Schema::hasColumn('bank_soal', 'id_sesi');
        $data->each(function ($sesi) use ($hasIdSesi) {
            if ($hasIdSesi && $sesi->plotting) {
                $countBySesi = BankSoal::where('id_sesi', $sesi->id_sesi)->count();
                $sesi->plotting->bank_soal_count = $countBySesi;
            }
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function storeSesiUjian(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_plotting'               => 'required|exists:plotting_mapel,id_plotting',
            'nama_sesi_ujian'           => 'required|string|max:150',
            'tanggal_ujian'             => 'required|date',
            'jam_mulai'                 => 'required',
            'jam_selesai'               => 'required',
            'durasi_menit'              => 'required|integer|min:10|max:300',
            'batas_toleransi_anticheat' => 'required|integer|min:1|max:10',
            'nama_pengawas_ruang'       => 'nullable|string|max:150',
            'catatan_berita_acara'      => 'nullable|string',
        ]);

        $profil = ProfilSekolah::firstOrFail();
        $plotting = PlottingMapel::with('guru')->findOrFail($validated['id_plotting']);

        $validated['id_admin'] = $request->user()->id_admin;
        $validated['id_profil'] = $profil->id_profil;
        $validated['status_sesi'] = 'tertutup';
        $validated['visibilitas_jadwal'] = 'tampil';
        $validated['publikasi_nilai'] = false;
        $validated['catatan_berita_acara'] = null;
        $validated['nama_pengawas_ruang'] = $validated['nama_pengawas_ruang'] ?? ($plotting->guru->nama_lengkap ?? '-');

        $sesi = SesiUjian::create($validated);

        // Sinkronkan langsung apabila jam yang baru dibuat sedang berlangsung sekarang
        $this->sinkronisasiJadwalWib();

        return response()->json([
            'status'  => 'success',
            'message' => 'Jadwal sesi ujian berhasil ditambahkan.',
            'data'    => $sesi->fresh()
        ], 201);
    }

    public function updateSesiUjian(Request $request, int $id): JsonResponse
    {
        $sesi = SesiUjian::findOrFail($id);

        $validated = $request->validate([
            'nama_sesi_ujian'           => 'required|string|max:150',
            'tanggal_ujian'             => 'required|date',
            'jam_mulai'                 => 'required',
            'jam_selesai'               => 'required',
            'durasi_menit'              => 'required|integer|min:10|max:300',
            'batas_toleransi_anticheat' => 'required|integer|min:1|max:10',
        ]);

        // Reset flag override saat jadwal diatur ulang agar kembali patuh pada jam otomatis
        $validated['catatan_berita_acara'] = null;
        $sesi->update($validated);

        $this->sinkronisasiJadwalWib();

        return response()->json([
            'status'  => 'success',
            'message' => 'Jadwal hari dan jam sesi ujian berhasil diperbarui dan disinkronkan.',
            'data'    => $sesi->fresh(),
        ]);
    }

    /**
     * Endpoint Saklar (Toggle) Sesi Ujian (Mendukung Override Buka Paksa di Luar Jam)
     * PATCH /api/admin/sesi-ujian/{id}/toggle
     */
    public function toggleSesiControls(Request $request, int $id): JsonResponse
    {
        $sesi = SesiUjian::findOrFail($id);

        $validated = $request->validate([
            'status_sesi'        => 'sometimes|in:terbuka,tertutup',
            'visibilitas_jadwal' => 'sometimes|in:tampil,tutup',
            'publikasi_nilai'    => 'sometimes|boolean',
            'force_override'     => 'nullable|boolean',
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $tglStr = Carbon::parse($sesi->tanggal_ujian)->format('Y-m-d');
        $jamMulaiStr = substr((string) $sesi->jam_mulai, 0, 5) . ':00';
        $jamSelesaiStr = substr((string) $sesi->jam_selesai, 0, 5) . ':59';

        $waktuMulai = Carbon::parse("{$tglStr} {$jamMulaiStr}", 'Asia/Jakarta');
        $waktuSelesai = Carbon::parse("{$tglStr} {$jamSelesaiStr}", 'Asia/Jakarta');
        $isWithinSchedule = $now->betweenIncluded($waktuMulai, $waktuSelesai);

        $updateData = collect($validated)->only(['status_sesi', 'visibilitas_jadwal', 'publikasi_nilai'])->toArray();

        if (isset($validated['status_sesi'])) {
            if ($validated['status_sesi'] === 'terbuka') {
                // Jika Admin membuka sesi di luar jam jadwal, tandai sebagai FORCE_OPEN_ADMIN
                $updateData['catatan_berita_acara'] = !$isWithinSchedule ? 'FORCE_OPEN_ADMIN' : null;
            } else {
                // Jika Admin menutup sesi saat masih di dalam jam jadwal, tandai sebagai FORCE_CLOSE_ADMIN
                $updateData['catatan_berita_acara'] = $isWithinSchedule ? 'FORCE_CLOSE_ADMIN' : null;
            }
        }

        $sesi->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan kontrol sesi ujian berhasil diperbarui.',
            'data'    => $sesi->fresh(),
        ]);
    }

    /**
     * Helper Internal: Bersihkan Seluruh Soal & Riwayat Ujian Milik 1 SesiUjian
     */
    private function bersihkanRelasiSesi(SesiUjian $sesi): void
    {
        foreach ($sesi->ujianMurid as $ujian) {
            $ujian->daftarJawaban()->delete();
            $ujian->logPelanggaran()->delete();
            $ujian->hasilNilai()->delete();
            $ujian->delete();
        }

        if (Schema::hasColumn('bank_soal', 'id_sesi')) {
            $soalSesi = BankSoal::where('id_sesi', $sesi->id_sesi)->get();
            foreach ($soalSesi as $s) {
                if ($s->path_gambar) {
                    Storage::disk('public')->delete($s->path_gambar);
                }
                $s->jawabanMurid()->delete();
                $s->delete();
            }
        }

        $sesi->delete();
    }

    /**
     * Hapus 1 Sesi Ujian Secara Permanen (Beserta Soal & Riwayat Ujian Terkait)
     * DELETE /api/admin/sesi-ujian/{id}
     */
    public function destroySesiUjian(int $id): JsonResponse
    {
        $sesi = SesiUjian::with('ujianMurid')->findOrFail($id);
        $namaSesi = $sesi->nama_sesi_ujian;

        DB::transaction(function () use ($sesi) {
            $this->bersihkanRelasiSesi($sesi);
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Jadwal sesi ujian '{$namaSesi}' berhasil dihapus secara permanen.",
        ]);
    }

    /**
     * Hapus Seluruh Jadwal Sesi Ujian Berdasarkan Hari (Senin s/d Sabtu atau ALL)
     * POST /api/admin/sesi-ujian/delete-by-day
     */
    public function destroySesiByDay(Request $request): JsonResponse
    {
        $hariTarget = $request->input('hari', 'ALL');

        $hariMap = [
            'Minggu' => 0,
            'Senin'  => 1,
            'Selasa' => 2,
            'Rabu'   => 3,
            'Kamis'  => 4,
            'Jumat'  => 5,
            'Sabtu'  => 6,
        ];

        $semuaSesi = SesiUjian::with('ujianMurid')->get();
        $deletedCount = 0;

        DB::transaction(function () use ($semuaSesi, $hariTarget, $hariMap, &$deletedCount) {
            foreach ($semuaSesi as $sesi) {
                $shouldDelete = false;

                if ($hariTarget === 'ALL') {
                    $shouldDelete = true;
                } elseif (isset($hariMap[$hariTarget])) {
                    $dayOfWeek = Carbon::parse($sesi->tanggal_ujian)->dayOfWeek;
                    if ($dayOfWeek === $hariMap[$hariTarget]) {
                        $shouldDelete = true;
                    }
                }

                if ($shouldDelete) {
                    $this->bersihkanRelasiSesi($sesi);
                    $deletedCount++;
                }
            }
        });

        $label = $hariTarget === 'ALL' ? 'Seluruh Hari' : "Hari {$hariTarget}";

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil menghapus {$deletedCount} jadwal sesi ujian pada {$label}.",
        ]);
    }

    /**
     * Hapus Seluruh Sesi Ujian Sekaligus
     * DELETE /api/admin/sesi-ujian/clear-all
     */
    public function destroyAllSesiUjian(): JsonResponse
    {
        $semuaSesi = SesiUjian::with('ujianMurid')->get();
        $count = $semuaSesi->count();

        DB::transaction(function () use ($semuaSesi) {
            foreach ($semuaSesi as $sesi) {
                $this->bersihkanRelasiSesi($sesi);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Seluruh jadwal sesi ujian ({$count} sesi) telah berhasil dihapus.",
        ]);
    }

    // =========================================================================
    // 6. PROFIL SEKOLAH
    // =========================================================================
    public function showProfilSekolah(): JsonResponse
    {
        return response()->json(['data' => ProfilSekolah::first()]);
    }

    public function updateProfilSekolah(Request $request): JsonResponse
    {
        $profil = ProfilSekolah::firstOrFail();

        $validated = $request->validate([
            'nama_sekolah'        => 'required|string|max:150',
            'npsn'                => 'required|string|max:20',
            'tahun_ajaran'        => 'required|string|max:20',
            'semester'            => 'required|in:Ganjil,Genap',
            'nama_kepala_sekolah' => 'nullable|string|max:150',
            'alamat_sekolah'      => 'nullable|string',
        ]);

        $profil->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil sekolah berhasil diperbarui.',
            'data'    => $profil
        ]);
    }
}