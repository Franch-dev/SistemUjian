<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PlottingMapel;
use App\Models\BankSoal;
use App\Models\Murid;
use App\Models\UjianMurid;
use App\Models\SesiUjian;
use App\Models\ProfilSekolah;
use App\Models\Admin;

class GuruCbtController extends Controller
{
    /**
     * Pastikan tabel bank_soal memiliki kolom id_sesi agar 1 Mapel/Kelas
     * bisa memiliki banyak Formulir Ujian (Paket Soal) yang terpisah.
     */
    private function ensureMultiFormSchema(): void
    {
        if (!Schema::hasColumn('bank_soal', 'id_sesi')) {
            Schema::table('bank_soal', function (Blueprint $table) {
                $table->unsignedBigInteger('id_sesi')->nullable()->after('id_plotting');
            });
        }

        // Hubungkan soal lama yang belum memiliki id_sesi ke formulir sesi_ujian-nya
        $orphanPlots = DB::table('bank_soal')->whereNull('id_sesi')->distinct()->pluck('id_plotting');
        foreach ($orphanPlots as $idPlot) {
            $sesi = SesiUjian::where('id_plotting', $idPlot)->first();
            if (!$sesi) {
                $plot = PlottingMapel::with(['mapel', 'kelas', 'guru'])->find($idPlot);
                $profil = ProfilSekolah::first();
                $admin = Admin::first();
                if ($plot && $profil && $admin) {
                    $sesi = SesiUjian::create([
                        'id_plotting'               => $plot->id_plotting,
                        'id_admin'                  => $admin->id_admin,
                        'id_profil'                 => $profil->id_profil,
                        'nama_sesi_ujian'           => 'Formulir Ujian — ' . ($plot->mapel->nama_mapel ?? 'Mapel') . ' (' . ($plot->kelas->nama_kelas ?? '') . ')',
                        'tanggal_ujian'             => today(),
                        'jam_mulai'                 => '07:30:00',
                        'jam_selesai'               => '16:00:00',
                        'durasi_menit'              => 90,
                        'batas_toleransi_anticheat' => 3,
                        'status_sesi'               => 'tertutup',
                        'visibilitas_jadwal'        => 'tampil',
                        'publikasi_nilai'           => false,
                        'nama_pengawas_ruang'       => $plot->guru->nama_lengkap ?? '-',
                    ]);
                }
            }

            if ($sesi) {
                DB::table('bank_soal')
                    ->where('id_plotting', $idPlot)
                    ->whereNull('id_sesi')
                    ->update(['id_sesi' => $sesi->id_sesi]);
            }
        }
    }

    /**
     * Daftar Kelas & Mata Pelajaran Ampuan + Daftar Seluruh Formulir Ujian Milik Guru
     * GET /api/guru/plotting-ampuan
     */
    public function indexPlottingAmpuan(Request $request): JsonResponse
    {
        $this->ensureMultiFormSchema();
        $guru = $request->user();

        $plotting = PlottingMapel::with([
                'mapel',
                'kelas.jurusan',
                'kelas.murid',
                'sesiUjian.ujianMurid'
            ])
            ->withCount('bankSoal')
            ->where('id_guru', $guru->id_guru)
            ->get();

        // Ambil seluruh Formulir Ujian (SesiUjian) milik Guru ini beserta butir soal masing-masing
        $formulirList = SesiUjian::with(['plotting.mapel', 'plotting.kelas.jurusan'])
            ->whereHas('plotting', fn($q) => $q->where('id_guru', $guru->id_guru))
            ->latest('id_sesi')
            ->get()
            ->map(function ($sesi) {
                $daftarSoal = BankSoal::where('id_sesi', $sesi->id_sesi)
                    ->orderBy('id_soal', 'asc')
                    ->get()
                    ->map(function ($s) {
                        $s->url_gambar = $s->path_gambar ? asset('storage/' . $s->path_gambar) : null;
                        return $s;
                    });

                return [
                    'id_sesi'         => $sesi->id_sesi,
                    'id_plotting'     => $sesi->id_plotting,
                    'nama_formulir'   => $sesi->nama_sesi_ujian,
                    'tanggal_ujian'   => $sesi->tanggal_ujian ? $sesi->tanggal_ujian->format('Y-m-d') : today()->format('Y-m-d'),
                    'durasi_menit'    => $sesi->durasi_menit,
                    'status_sesi'     => $sesi->status_sesi,
                    'plotting'        => $sesi->plotting,
                    'daftar_soal'     => $daftarSoal,
                    'jumlah_soal'     => $daftarSoal->count(),
                    'total_bobot'     => $daftarSoal->sum('bobot_soal'),
                ];
            });

        return response()->json([
            'status'        => 'success',
            'guru'          => [
                'id_guru'      => $guru->id_guru,
                'kode_guru'    => $guru->kode_guru,
                'nama_lengkap' => $guru->nama_lengkap,
                'nip'          => $guru->nip,
            ],
            'data'          => $plotting,
            'formulir_list' => $formulirList,
        ]);
    }

    /**
     * Daftar Butir Soal Berdasarkan Plotting / Formulir Sesi
     * GET /api/guru/plotting/{id_plotting}/bank-soal
     */
    public function indexBankSoal(Request $request, int $id_plotting): JsonResponse
    {
        $this->ensureMultiFormSchema();
        $guru = $request->user();

        $plotting = PlottingMapel::where('id_plotting', $id_plotting)
            ->where('id_guru', $guru->id_guru)
            ->with(['mapel', 'kelas.jurusan'])
            ->firstOrFail();

        $query = BankSoal::where('id_plotting', $plotting->id_plotting);
        if ($request->filled('id_sesi')) {
            $query->where('id_sesi', $request->query('id_sesi'));
        }

        $soal = $query->orderBy('id_soal', 'asc')->get()->map(function ($s) {
            $s->url_gambar = $s->path_gambar ? asset('storage/' . $s->path_gambar) : null;
            return $s;
        });

        return response()->json([
            'status'   => 'success',
            'plotting' => $plotting,
            'data'     => $soal,
        ]);
    }

    /**
     * Upload Gambar untuk Opsi Jawaban (A-E) atau Pertanyaan pada Google Form Builder
     * POST /api/guru/bank-soal/upload-media
     */
    public function uploadMediaOpsi(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('file')->store('soal_images', 'public');

        return response()->json([
            'status' => 'success',
            'path'   => $path,
            'url'    => asset('storage/' . $path),
        ]);
    }

    /**
     * Simpan Formulir Ujian Baru ATAU Simpan Perubahan pada Formulir Ujian Tertentu
     * POST /api/guru/plotting/{id_plotting}/bank-soal/bulk-save
     */
    public function bulkSaveBankSoal(Request $request, int $id_plotting): JsonResponse
    {
        $this->ensureMultiFormSchema();
        $guru = $request->user();

        $plotting = PlottingMapel::with(['mapel', 'kelas'])
            ->where('id_plotting', $id_plotting)
            ->where('id_guru', $guru->id_guru)
            ->firstOrFail();

        $validated = $request->validate([
            'id_sesi'               => 'nullable|integer',
            'nama_formulir'         => 'required|string|max:150',
            'items'                 => 'required|array|min:1',
            'items.*.id_soal'       => 'nullable|integer',
            'items.*.pertanyaan'    => 'required|string',
            'items.*.path_gambar'   => 'nullable|string',
            'items.*.opsi_a'        => 'required|string',
            'items.*.opsi_b'        => 'required|string',
            'items.*.opsi_c'        => 'required|string',
            'items.*.opsi_d'        => 'required|string',
            'items.*.opsi_e'        => 'required|string',
            'items.*.kunci_jawaban' => 'required|in:A,B,C,D,E',
            'items.*.bobot_soal'    => 'required|integer|min:1|max:100',
        ]);

        $profil = ProfilSekolah::firstOrFail();
        $admin = Admin::firstOrFail();

        return DB::transaction(function () use ($validated, $plotting, $guru, $profil, $admin) {
            $sesi = null;

            // Jika id_sesi dikirim -> Edit Formulir Ujian yang sudah ada
            if (!empty($validated['id_sesi'])) {
                $sesi = SesiUjian::where('id_sesi', $validated['id_sesi'])
                    ->where('id_plotting', $plotting->id_plotting)
                    ->first();
            }

            if ($sesi) {
                $sesi->update([
                    'nama_sesi_ujian' => trim($validated['nama_formulir']),
                ]);
            } else {
                // Jika id_sesi kosong -> BUAT FORMULIR UJIAN BARU (Terpisah dari formulir sebelumnya!)
                $sesi = SesiUjian::create([
                    'id_plotting'               => $plotting->id_plotting,
                    'id_admin'                  => $admin->id_admin,
                    'id_profil'                 => $profil->id_profil,
                    'nama_sesi_ujian'           => trim($validated['nama_formulir']),
                    'tanggal_ujian'             => today(),
                    'jam_mulai'                 => '07:30:00',
                    'jam_selesai'               => '16:00:00',
                    'durasi_menit'              => 90,
                    'batas_toleransi_anticheat' => 3,
                    'status_sesi'               => 'tertutup',
                    'visibilitas_jadwal'        => 'tampil',
                    'publikasi_nilai'           => false,
                    'nama_pengawas_ruang'       => $guru->nama_lengkap,
                ]);
            }

            $keptIds = [];

            foreach ($validated['items'] as $item) {
                if (!empty($item['id_soal'])) {
                    $soal = BankSoal::where('id_plotting', $plotting->id_plotting)
                        ->where('id_sesi', $sesi->id_sesi)
                        ->where('id_soal', $item['id_soal'])
                        ->first();

                    if ($soal) {
                        $soal->update([
                            'pertanyaan'    => $item['pertanyaan'],
                            'path_gambar'   => $item['path_gambar'] ?? null,
                            'opsi_a'        => $item['opsi_a'],
                            'opsi_b'        => $item['opsi_b'],
                            'opsi_c'        => $item['opsi_c'],
                            'opsi_d'        => $item['opsi_d'],
                            'opsi_e'        => $item['opsi_e'],
                            'kunci_jawaban' => $item['kunci_jawaban'],
                            'bobot_soal'    => $item['bobot_soal'],
                        ]);
                        $keptIds[] = $soal->id_soal;
                        continue;
                    }
                }

                $baru = new BankSoal([
                    'id_plotting'   => $plotting->id_plotting,
                    'pertanyaan'    => $item['pertanyaan'],
                    'path_gambar'   => $item['path_gambar'] ?? null,
                    'opsi_a'        => $item['opsi_a'],
                    'opsi_b'        => $item['opsi_b'],
                    'opsi_c'        => $item['opsi_c'],
                    'opsi_d'        => $item['opsi_d'],
                    'opsi_e'        => $item['opsi_e'],
                    'kunci_jawaban' => $item['kunci_jawaban'],
                    'bobot_soal'    => $item['bobot_soal'],
                ]);
                $baru->id_sesi = $sesi->id_sesi;
                $baru->save();

                $keptIds[] = $baru->id_soal;
            }

            // Hapus hanya butir soal yang dibuang dari formulir (id_sesi) ini saja
            BankSoal::where('id_sesi', $sesi->id_sesi)
                ->whereNotIn('id_soal', $keptIds)
                ->delete();

            return response()->json([
                'status'  => 'success',
                'message' => "Formulir ujian '{$sesi->nama_sesi_ujian}' (" . count($keptIds) . " butir soal) berhasil disimpan!",
                'id_sesi' => $sesi->id_sesi,
                'total'   => count($keptIds),
            ]);
        });
    }

    /**
     * Hapus Formulir Ujian Tertentu Beserta Seluruh Butir Soalnya
     * DELETE /api/guru/plotting/{id_plotting}/bank-soal/clear-all
     */
    public function clearAllBankSoal(Request $request, int $id_plotting): JsonResponse
    {
        $this->ensureMultiFormSchema();
        $guru = $request->user();

        $plotting = PlottingMapel::where('id_plotting', $id_plotting)
            ->where('id_guru', $guru->id_guru)
            ->firstOrFail();

        $idSesi = $request->query('id_sesi');

        return DB::transaction(function () use ($plotting, $idSesi) {
            if ($idSesi) {
                $sesi = SesiUjian::where('id_sesi', $idSesi)
                    ->where('id_plotting', $plotting->id_plotting)
                    ->with('ujianMurid')
                    ->firstOrFail();

                $namaForm = $sesi->nama_sesi_ujian;
                $daftarSoal = BankSoal::where('id_sesi', $sesi->id_sesi)->get();

                foreach ($daftarSoal as $s) {
                    if ($s->path_gambar) {
                        Storage::disk('public')->delete($s->path_gambar);
                    }
                    foreach ($s->jawabanMurid ?? [] as $jwb) {
                        $jwb->delete();
                    }
                    $s->delete();
                }

                foreach ($sesi->ujianMurid as $ujian) {
                    $ujian->daftarJawaban()->delete();
                    $ujian->logPelanggaran()->delete();
                    $ujian->hasilNilai()->delete();
                    $ujian->delete();
                }

                $sesi->delete();

                return response()->json([
                    'status'  => 'success',
                    'message' => "Formulir ujian '{$namaForm}' beserta seluruh soalnya berhasil dihapus.",
                ]);
            }

            // Fallback jika menghapus semua soal pada plotting
            $daftarSoal = BankSoal::where('id_plotting', $plotting->id_plotting)->get();
            foreach ($daftarSoal as $s) {
                if ($s->path_gambar) {
                    Storage::disk('public')->delete($s->path_gambar);
                }
            }
            $deletedCount = BankSoal::where('id_plotting', $plotting->id_plotting)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => "Seluruh soal ({$deletedCount} butir soal) berhasil dihapus.",
            ]);
        });
    }

    public function storeBankSoal(Request $request, int $id_plotting): JsonResponse
    {
        $this->ensureMultiFormSchema();
        $guru = $request->user();
        $plotting = PlottingMapel::where('id_plotting', $id_plotting)
            ->where('id_guru', $guru->id_guru)
            ->firstOrFail();

        $validated = $request->validate([
            'pertanyaan'    => 'required|string',
            'gambar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'opsi_a'        => 'required|string',
            'opsi_b'        => 'required|string',
            'opsi_c'        => 'required|string',
            'opsi_d'        => 'required|string',
            'opsi_e'        => 'required|string',
            'kunci_jawaban' => 'required|in:A,B,C,D,E',
            'bobot_soal'    => 'required|integer|min:1|max:100',
        ]);

        $pathGambar = $request->hasFile('gambar')
            ? $request->file('gambar')->store('soal_images', 'public')
            : null;

        $soal = BankSoal::create([
            'id_plotting'   => $plotting->id_plotting,
            'pertanyaan'    => $validated['pertanyaan'],
            'path_gambar'   => $pathGambar,
            'opsi_a'        => $validated['opsi_a'],
            'opsi_b'        => $validated['opsi_b'],
            'opsi_c'        => $validated['opsi_c'],
            'opsi_d'        => $validated['opsi_d'],
            'opsi_e'        => $validated['opsi_e'],
            'kunci_jawaban' => $validated['kunci_jawaban'],
            'bobot_soal'    => $validated['bobot_soal'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Butir soal berhasil ditambahkan.',
            'data'    => $soal,
        ], 201);
    }

    public function updateBankSoal(Request $request, int $id_soal): JsonResponse
    {
        $guru = $request->user();
        $soal = BankSoal::whereHas('plotting', fn($q) => $q->where('id_guru', $guru->id_guru))
            ->findOrFail($id_soal);

        $validated = $request->validate([
            'pertanyaan'    => 'required|string',
            'gambar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hapus_gambar'  => 'nullable|boolean',
            'opsi_a'        => 'required|string',
            'opsi_b'        => 'required|string',
            'opsi_c'        => 'required|string',
            'opsi_d'        => 'required|string',
            'opsi_e'        => 'required|string',
            'kunci_jawaban' => 'required|in:A,B,C,D,E',
            'bobot_soal'    => 'required|integer|min:1|max:100',
        ]);

        if (!empty($validated['hapus_gambar']) && $soal->path_gambar) {
            Storage::disk('public')->delete($soal->path_gambar);
            $soal->path_gambar = null;
        }

        if ($request->hasFile('gambar')) {
            if ($soal->path_gambar) {
                Storage::disk('public')->delete($soal->path_gambar);
            }
            $soal->path_gambar = $request->file('gambar')->store('soal_images', 'public');
        }

        $soal->update([
            'pertanyaan'    => $validated['pertanyaan'],
            'path_gambar'   => $soal->path_gambar,
            'opsi_a'        => $validated['opsi_a'],
            'opsi_b'        => $validated['opsi_b'],
            'opsi_c'        => $validated['opsi_c'],
            'opsi_d'        => $validated['opsi_d'],
            'opsi_e'        => $validated['opsi_e'],
            'kunci_jawaban' => $validated['kunci_jawaban'],
            'bobot_soal'    => $validated['bobot_soal'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Butir soal berhasil diperbarui.',
            'data'    => $soal,
        ]);
    }

    public function destroyBankSoal(Request $request, int $id_soal): JsonResponse
    {
        $guru = $request->user();
        $soal = BankSoal::whereHas('plotting', fn($q) => $q->where('id_guru', $guru->id_guru))
            ->findOrFail($id_soal);

        if ($soal->path_gambar) {
            Storage::disk('public')->delete($soal->path_gambar);
        }

        $soal->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Butir soal berhasil dihapus.',
        ]);
    }

    public function rekapNilaiAmpuan(Request $request, int $id_plotting): JsonResponse
    {
        $guru = $request->user();
        $plotting = PlottingMapel::where('id_plotting', $id_plotting)
            ->where('id_guru', $guru->id_guru)
            ->with(['mapel', 'kelas.jurusan', 'sesiUjian'])
            ->firstOrFail();

        $semuaMuridKelas = Murid::where('id_kelas', $plotting->id_kelas)
            ->orderBy('nama_lengkap')
            ->get();

        $rekapPeserta = UjianMurid::with(['murid', 'sesiUjian', 'hasilNilai', 'logPelanggaran'])
            ->whereHas('sesiUjian', fn($q) => $q->where('id_plotting', $plotting->id_plotting))
            ->get();

        return response()->json([
            'status'            => 'success',
            'plotting'          => $plotting,
            'mapel'             => $plotting->mapel->nama_mapel,
            'kode_mapel'        => $plotting->mapel->kode_mapel,
            'kelas'             => $plotting->kelas->nama_kelas,
            'nilai_kkm'         => $plotting->mapel->nilai_kkm,
            'semua_murid_kelas' => $semuaMuridKelas,
            'data'              => $rekapPeserta,
        ]);
    }
}