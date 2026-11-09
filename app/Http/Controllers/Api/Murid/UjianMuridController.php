<?php

namespace App\Http\Controllers\Api\Murid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\SesiUjian;
use App\Models\UjianMurid;
use App\Models\JawabanMurid;
use App\Models\BankSoal;
use App\Models\LogPelanggaran;

class UjianMuridController extends Controller
{
    /**
     * 1. Daftar Jadwal Ujian Aktif Sesuai Kelas Murid (Otomatis Buka/Tutup Sesuai Jam WIB)
     * GET /api/murid/jadwal-ujian
     */
    public function jadwalAktif(Request $request): JsonResponse
    {
        $murid = $request->user();
        $now = Carbon::now('Asia/Jakarta');

        $jadwal = SesiUjian::with(['plotting.mapel', 'plotting.guru'])
            ->where('visibilitas_jadwal', 'tampil')
            ->whereHas('plotting', function ($q) use ($murid) {
                $q->where('id_kelas', $murid->id_kelas);
            })
            ->orderBy('tanggal_ujian', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get()
            ->map(function ($sesi) use ($murid, $now) {
                $tglStr = Carbon::parse($sesi->tanggal_ujian)->format('Y-m-d');
                $jamMulaiStr = substr((string) $sesi->jam_mulai, 0, 5) . ':00';
                $jamSelesaiStr = substr((string) $sesi->jam_selesai, 0, 5) . ':59';

                $waktuMulai = Carbon::parse("{$tglStr} {$jamMulaiStr}", 'Asia/Jakarta');
                $waktuSelesai = Carbon::parse("{$tglStr} {$jamSelesaiStr}", 'Asia/Jakarta');

                $isWithinSchedule = $now->betweenIncluded($waktuMulai, $waktuSelesai);
                $isBeforeSchedule = $now->lt($waktuMulai);
                $isPastSchedule = $now->gt($waktuSelesai);
                $catatan = $sesi->catatan_berita_acara ?? '';

                // Sinkronisasi otomatis status_sesi berdasarkan jam WIB
                if ($isWithinSchedule && $sesi->status_sesi !== 'terbuka' && $catatan !== 'FORCE_CLOSE_ADMIN') {
                    $sesi->update(['status_sesi' => 'terbuka']);
                } elseif (!$isWithinSchedule && $sesi->status_sesi === 'terbuka' && $catatan !== 'FORCE_OPEN_ADMIN') {
                    $sesi->update(['status_sesi' => 'tertutup']);
                }

                $ujianMurid = UjianMurid::where('id_sesi', $sesi->id_sesi)
                    ->where('id_murid', $murid->id_murid)
                    ->first();

                // Jika waktu sudah lewat jam selesai dan tidak di-override Admin, kunci progres murid yang masih mengerjakan
                if ($ujianMurid && $ujianMurid->status_ujian === 'mengerjakan' && $isPastSchedule && $catatan !== 'FORCE_OPEN_ADMIN') {
                    $ujianMurid->update([
                        'status_ujian'     => 'auto_submit_waktu',
                        'sisa_waktu_detik' => 0,
                        'fullscreen_lock'  => false,
                    ]);
                }

                // Tentukan status ketersediaan waktu bagi murid
                $statusWaktu = 'dalam_jadwal';
                if ($catatan === 'FORCE_OPEN_ADMIN' && $sesi->status_sesi === 'terbuka') {
                    $statusWaktu = 'dibuka_paksa_admin';
                } elseif ($isBeforeSchedule) {
                    $statusWaktu = 'belum_mulai_jam';
                } elseif ($isPastSchedule) {
                    $statusWaktu = 'lewat_jam_selesai';
                }

                return [
                    'id_sesi'                   => $sesi->id_sesi,
                    'nama_sesi_ujian'           => $sesi->nama_sesi_ujian,
                    'nama_mapel'                => $sesi->plotting->mapel->nama_mapel,
                    'nilai_kkm'                 => $sesi->plotting->mapel->nilai_kkm,
                    'guru_pengampu'             => $sesi->plotting->guru->nama_lengkap,
                    'tanggal_ujian'             => $tglStr,
                    'jam_mulai'                 => $sesi->jam_mulai,
                    'jam_selesai'               => $sesi->jam_selesai,
                    'durasi_menit'              => $sesi->durasi_menit,
                    'batas_toleransi_anticheat' => $sesi->batas_toleransi_anticheat,
                    'status_sesi'               => $sesi->status_sesi,
                    'status_waktu'              => $statusWaktu,
                    'progres_murid'             => $ujianMurid ? [
                        'id_ujian_murid'   => $ujianMurid->id_ujian_murid,
                        'status_ujian'     => $ujianMurid->status_ujian,
                        'sisa_waktu_detik' => $ujianMurid->sisa_waktu_detik,
                        'pelanggaran'      => $ujianMurid->jumlah_pelanggaran,
                    ] : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $jadwal,
        ]);
    }

    /**
     * 2. Mulai Ujian Baru ATAU Lanjutkan Ujian (Validasi Ketat Jam Mulai & Jam Selesai WIB)
     * POST /api/murid/sesi-ujian/{id_sesi}/mulai
     */
    public function mulaiAtauResumeUjian(Request $request, int $id_sesi): JsonResponse
    {
        $request->validate([
            'pakta_integritas' => 'required|boolean|accepted',
            'fullscreen_lock'  => 'required|boolean|accepted',
        ]);

        $murid = $request->user();
        $sesi = SesiUjian::with('plotting')->findOrFail($id_sesi);

        $now = Carbon::now('Asia/Jakarta');
        $tglStr = Carbon::parse($sesi->tanggal_ujian)->format('Y-m-d');
        $jamMulaiStr = substr((string) $sesi->jam_mulai, 0, 5) . ':00';
        $jamSelesaiStr = substr((string) $sesi->jam_selesai, 0, 5) . ':59';

        $waktuMulai = Carbon::parse("{$tglStr} {$jamMulaiStr}", 'Asia/Jakarta');
        $waktuSelesai = Carbon::parse("{$tglStr} {$jamSelesaiStr}", 'Asia/Jakarta');
        $catatan = $sesi->catatan_berita_acara ?? '';

        // Validasi jam ujian jika tidak sedang di-override (FORCE_OPEN_ADMIN) oleh Admin
        if ($catatan !== 'FORCE_OPEN_ADMIN') {
            if ($now->lt($waktuMulai)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Waktu ujian belum dimulai. Jadwal ujian ini dibuka otomatis pada pukul ' . substr((string) $sesi->jam_mulai, 0, 5) . ' WIB.',
                ], 403);
            }

            if ($now->gt($waktuSelesai)) {
                $sesi->update(['status_sesi' => 'tertutup']);
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Waktu pelaksanaan ujian telah berakhir pada pukul ' . substr((string) $sesi->jam_selesai, 0, 5) . ' WIB.',
                ], 403);
            }

            // Jika di dalam jam jadwal dan tidak ditutup paksa Admin, pastikan terbuka
            if ($sesi->status_sesi !== 'terbuka' && $catatan !== 'FORCE_CLOSE_ADMIN') {
                $sesi->update(['status_sesi' => 'terbuka']);
            }
        }

        if ($sesi->status_sesi !== 'terbuka' || $sesi->plotting->id_kelas !== $murid->id_kelas) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi ujian sedang ditutup oleh Administrator atau bukan untuk kelas Anda.',
            ], 403);
        }

        $ujianMurid = UjianMurid::where('id_sesi', $sesi->id_sesi)
            ->where('id_murid', $murid->id_murid)
            ->first();

        if ($ujianMurid && in_array($ujianMurid->status_ujian, [
            'selesai_mandiri',
            'auto_submit_waktu',
            'auto_submit_pelanggaran',
            'diskualifikasi'
        ], true)) {
            return response()->json([
                'status'  => 'finished',
                'message' => 'Anda sudah menyelesaikan ujian ini.',
            ], 403);
        }

        // Hitung sisa detik maksimal (tidak boleh melebihi jam_selesai kecuali FORCE_OPEN_ADMIN)
        $durasiDetikPenuh = $sesi->durasi_menit * 60;
        if ($catatan !== 'FORCE_OPEN_ADMIN') {
            $detikMenujuJamSelesai = max(0, $now->diffInSeconds($waktuSelesai, false));
            $batasDetikMaksimal = min($durasiDetikPenuh, $detikMenujuJamSelesai);
        } else {
            $batasDetikMaksimal = $durasiDetikPenuh;
        }

        if (!$ujianMurid) {
            $ujianMurid = UjianMurid::create([
                'id_sesi'            => $sesi->id_sesi,
                'id_murid'           => $murid->id_murid,
                'pakta_integritas'   => true,
                'fullscreen_lock'    => true,
                'sisa_waktu_detik'   => $batasDetikMaksimal,
                'jumlah_pelanggaran' => 0,
                'status_ujian'       => 'mengerjakan',
                'waktu_mulai'        => now(),
            ]);
            $isResumed = false;
        } else {
            $sisaDetikResume = $catatan !== 'FORCE_OPEN_ADMIN'
                ? min($ujianMurid->sisa_waktu_detik, $batasDetikMaksimal)
                : ($ujianMurid->sisa_waktu_detik > 0 ? $ujianMurid->sisa_waktu_detik : $durasiDetikPenuh);

            $ujianMurid->update([
                'pakta_integritas' => true,
                'fullscreen_lock'  => true,
                'sisa_waktu_detik' => $sisaDetikResume,
                'status_ujian'     => 'mengerjakan',
            ]);
            $isResumed = true;
        }

        return response()->json([
            'status'      => 'success',
            'is_resumed'  => $isResumed,
            'message'     => $isResumed
                ? 'Ujian berhasil dilanjutkan (Resumed). Sisa waktu dan jawaban sebelumnya telah dipulihkan.'
                : 'Pakta integritas disetujui. Selamat mengerjakan ujian!',
            'ujian_murid' => $ujianMurid,
        ]);
    }

    /**
     * 3. Ambil Lembar Soal & Jawaban Tersimpan (Mendukung Soal Per Formulir id_sesi)
     * GET /api/murid/ujian/{id_ujian_murid}/lembar-soal
     */
    public function getLembarSoal(Request $request, int $id_ujian_murid): JsonResponse
    {
        $murid = $request->user();
        $ujianMurid = UjianMurid::with(['sesiUjian.plotting.mapel'])
            ->where('id_ujian_murid', $id_ujian_murid)
            ->where('id_murid', $murid->id_murid)
            ->firstOrFail();

        $sesi = $ujianMurid->sesiUjian;
        $now = Carbon::now('Asia/Jakarta');
        $tglStr = Carbon::parse($sesi->tanggal_ujian)->format('Y-m-d');
        $jamSelesaiStr = substr((string) $sesi->jam_selesai, 0, 5) . ':59';
        $waktuSelesai = Carbon::parse("{$tglStr} {$jamSelesaiStr}", 'Asia/Jakarta');
        $catatan = $sesi->catatan_berita_acara ?? '';

        // Jika waktu sekarang sudah melewati jam_selesai dan tidak di-override Admin, kunci otomatis!
        if ($catatan !== 'FORCE_OPEN_ADMIN' && $now->gt($waktuSelesai)) {
            if ($ujianMurid->status_ujian === 'mengerjakan') {
                $ujianMurid->update([
                    'status_ujian'     => 'auto_submit_waktu',
                    'sisa_waktu_detik' => 0,
                    'fullscreen_lock'  => false,
                ]);
            }
            $sesi->update(['status_sesi' => 'tertutup']);

            return response()->json([
                'status'  => 'closed',
                'message' => 'Waktu pelaksanaan ujian telah berakhir pada pukul ' . substr((string) $sesi->jam_selesai, 0, 5) . ' WIB. Jawaban Anda telah dikumpulkan secara otomatis.',
            ], 403);
        }

        if ($ujianMurid->status_ujian !== 'mengerjakan' || $sesi->status_sesi !== 'terbuka') {
            return response()->json([
                'status'  => 'closed',
                'message' => 'Sesi pengerjaan Anda sudah tidak aktif atau telah ditutup.',
            ], 403);
        }

        // Sesuaikan sisa_waktu_detik agar tidak melebihi jam_selesai
        if ($catatan !== 'FORCE_OPEN_ADMIN') {
            $detikKeSelesai = max(0, $now->diffInSeconds($waktuSelesai, false));
            if ($ujianMurid->sisa_waktu_detik > $detikKeSelesai) {
                $ujianMurid->update(['sisa_waktu_detik' => $detikKeSelesai]);
            }
        }

        // Ambil butir soal sesuai Formulir Sesi Ujian (id_sesi) yang dikerjakan
        $querySoal = BankSoal::select([
                'id_soal',
                'id_plotting',
                'pertanyaan',
                'path_gambar',
                'opsi_a',
                'opsi_b',
                'opsi_c',
                'opsi_d',
                'opsi_e',
                'bobot_soal',
            ])
            ->where('id_plotting', $sesi->id_plotting);

        if (Schema::hasColumn('bank_soal', 'id_sesi')) {
            $hasSesiQuestions = BankSoal::where('id_sesi', $sesi->id_sesi)->exists();
            if ($hasSesiQuestions) {
                $querySoal->where('id_sesi', $sesi->id_sesi);
            }
        }

        $daftarSoal = $querySoal->orderBy('id_soal', 'asc')->get();

        $jawabanTersimpan = JawabanMurid::where('id_ujian_murid', $ujianMurid->id_ujian_murid)
            ->get()
            ->keyBy('id_soal');

        $lembarSoal = $daftarSoal->map(function ($soal, $index) use ($jawabanTersimpan) {
            $jwb = $jawabanTersimpan->get($soal->id_soal);
            return [
                'nomor_urut'      => $index + 1,
                'id_soal'         => $soal->id_soal,
                'pertanyaan'      => $soal->pertanyaan,
                'url_gambar'      => $soal->path_gambar ? asset('storage/' . $soal->path_gambar) : null,
                'opsi'            => [
                    'A' => $soal->opsi_a,
                    'B' => $soal->opsi_b,
                    'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d,
                    'E' => $soal->opsi_e,
                ],
                'pilihan_jawaban' => $jwb?->pilihan_jawaban,
                'status_ragu'     => (bool) ($jwb?->status_ragu ?? false),
                'waktu_autosave'  => $jwb?->waktu_autosave,
            ];
        });

        return response()->json([
            'status'                    => 'success',
            'id_ujian_murid'            => $ujianMurid->id_ujian_murid,
            'nama_mapel'                => $sesi->plotting->mapel->nama_mapel . ' — ' . $sesi->nama_sesi_ujian,
            'sisa_waktu_detik'          => $ujianMurid->sisa_waktu_detik,
            'jumlah_pelanggaran'        => $ujianMurid->jumlah_pelanggaran,
            'batas_toleransi_anticheat' => $sesi->batas_toleransi_anticheat,
            'total_soal'                => $lembarSoal->count(),
            'lembar_soal'               => $lembarSoal,
        ]);
    }

    /**
     * 4. Real-Time Auto-Save Jawaban per Klik Opsi (A-E), Tandai Ragu-Ragu, & Sinkronisasi Timer
     * POST /api/murid/ujian/{id_ujian_murid}/autosave
     */
    public function autoSaveJawaban(Request $request, int $id_ujian_murid): JsonResponse
    {
        $validated = $request->validate([
            'id_soal'          => 'required|exists:bank_soal,id_soal',
            'pilihan_jawaban'  => 'nullable|in:A,B,C,D,E',
            'status_ragu'      => 'required|boolean',
            'sisa_waktu_detik' => 'required|integer|min:0',
        ]);

        $murid = $request->user();
        $ujianMurid = UjianMurid::where('id_ujian_murid', $id_ujian_murid)
            ->where('id_murid', $murid->id_murid)
            ->where('status_ujian', 'mengerjakan')
            ->firstOrFail();

        $jawaban = JawabanMurid::updateOrCreate(
            [
                'id_ujian_murid' => $ujianMurid->id_ujian_murid,
                'id_soal'        => $validated['id_soal'],
            ],
            [
                'pilihan_jawaban' => $validated['pilihan_jawaban'],
                'status_ragu'     => $validated['status_ragu'],
                'waktu_autosave'  => now(),
            ]
        );

        $ujianMurid->update([
            'sisa_waktu_detik' => $validated['sisa_waktu_detik'],
        ]);

        return response()->json([
            'status'         => 'saved',
            'message'        => 'Jawaban otomatis tersimpan.',
            'waktu_autosave' => $jawaban->waktu_autosave->format('H:i:s'),
        ]);
    }

    /**
     * 5. Catat Pelanggaran Anti-Cheat (Keluar Fullscreen / Pindah Tab Browser)
     * POST /api/murid/ujian/{id_ujian_murid}/lapor-pelanggaran
     */
    public function laporPelanggaran(Request $request, int $id_ujian_murid): JsonResponse
    {
        $validated = $request->validate([
            'jenis_pelanggaran' => 'required|string|max:100',
            'sisa_waktu_detik'  => 'nullable|integer|min:0',
        ]);

        $murid = $request->user();
        $ujianMurid = UjianMurid::with('sesiUjian')
            ->where('id_ujian_murid', $id_ujian_murid)
            ->where('id_murid', $murid->id_murid)
            ->firstOrFail();

        if ($ujianMurid->status_ujian !== 'mengerjakan') {
            return response()->json([
                'status'       => 'already_closed',
                'status_ujian' => $ujianMurid->status_ujian,
            ]);
        }

        LogPelanggaran::create([
            'id_ujian_murid'    => $ujianMurid->id_ujian_murid,
            'jenis_pelanggaran' => $validated['jenis_pelanggaran'],
            'waktu_pelanggaran' => now(),
        ]);

        $ujianMurid->jumlah_pelanggaran += 1;
        if (isset($validated['sisa_waktu_detik'])) {
            $ujianMurid->sisa_waktu_detik = $validated['sisa_waktu_detik'];
        }
        $ujianMurid->save();

        $ujianMurid->refresh();
        $batasMax = $ujianMurid->sesiUjian->batas_toleransi_anticheat;
        $isAutoSubmitted = ($ujianMurid->status_ujian === 'auto_submit_pelanggaran');

        return response()->json([
            'status'             => $isAutoSubmitted ? 'auto_submitted' : 'warning',
            'message'            => $isAutoSubmitted
                ? "Batas toleransi pelanggaran ({$ujianMurid->jumlah_pelanggaran}/{$batasMax}) telah habis! Sistem menghentikan dan mengumpulkan ujian Anda secara otomatis."
                : "Peringatan Anti-Cheat ({$ujianMurid->jumlah_pelanggaran}/{$batasMax}): Terdeteksi {$validated['jenis_pelanggaran']}.",
            'jumlah_pelanggaran' => $ujianMurid->jumlah_pelanggaran,
            'batas_toleransi'    => $batasMax,
            'status_ujian'       => $ujianMurid->status_ujian,
        ]);
    }

    /**
     * 6. Submit Ujian (Selesai Mandiri atau Waktu Habis)
     * POST /api/murid/ujian/{id_ujian_murid}/submit
     */
    public function submitUjian(Request $request, int $id_ujian_murid): JsonResponse
    {
        $validated = $request->validate([
            'tipe_submit' => 'nullable|in:selesai_mandiri,auto_submit_waktu',
        ]);

        $murid = $request->user();
        $ujianMurid = UjianMurid::with('sesiUjian')
            ->where('id_ujian_murid', $id_ujian_murid)
            ->where('id_murid', $murid->id_murid)
            ->firstOrFail();

        $statusAkhir = $validated['tipe_submit'] ?? 'selesai_mandiri';

        $ujianMurid->update([
            'status_ujian'     => $statusAkhir,
            'sisa_waktu_detik' => 0,
            'fullscreen_lock'  => false,
        ]);

        $hasil = $ujianMurid->fresh()->hasilNilai;
        $bolehLihatNilai = (bool) $ujianMurid->sesiUjian->publikasi_nilai;

        return response()->json([
            'status'          => 'success',
            'message'         => 'Ujian berhasil dikumpulkan.',
            'status_ujian'    => $ujianMurid->status_ujian,
            'publikasi_nilai' => $bolehLihatNilai,
            'hasil_nilai'     => $bolehLihatNilai ? $hasil : 'Detail nilai akhir menunggu publikasi oleh Administrator.',
        ]);
    }

    /**
     * 7. Riwayat Nilai & Hasil Ujian Murid (Hanya Menampilkan Skor Jika publikasi_nilai == true)
     * GET /api/murid/riwayat-nilai
     */
    public function riwayatNilai(Request $request): JsonResponse
    {
        $murid = $request->user();

        $riwayat = UjianMurid::with(['sesiUjian.plotting.mapel', 'sesiUjian.plotting.guru', 'hasilNilai'])
            ->where('id_murid', $murid->id_murid)
            ->where('status_ujian', '!=', 'belum_mulai')
            ->latest()
            ->get()
            ->map(function ($item) {
                $dipublikasi = (bool) $item->sesiUjian->publikasi_nilai;

                return [
                    'id_ujian_murid'  => $item->id_ujian_murid,
                    'nama_sesi_ujian' => $item->sesiUjian->nama_sesi_ujian,
                    'nama_mapel'      => $item->sesiUjian->plotting->mapel->nama_mapel,
                    'guru_pengampu'   => $item->sesiUjian->plotting->guru->nama_lengkap,
                    'nilai_kkm'       => $item->sesiUjian->plotting->mapel->nilai_kkm,
                    'status_ujian'    => $item->status_ujian,
                    'publikasi_nilai' => $dipublikasi,
                    'hasil'           => $dipublikasi && $item->hasilNilai ? [
                        'jumlah_benar'     => $item->hasilNilai->jumlah_benar,
                        'nilai_akhir'      => $item->hasilNilai->nilai_akhir,
                        'status_kelulusan' => $item->hasilNilai->status_kelulusan,
                        'waktu_selesai'    => $item->hasilNilai->waktu_selesai->format('Y-m-d H:i:s'),
                    ] : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $riwayat,
        ]);
    }
}   