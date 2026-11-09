<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Murid;
use App\Models\Guru;
use App\Models\MasterJurusan;
use App\Models\PlottingMapel;
use App\Models\SesiUjian;
use App\Models\SesiLoginMurid;
use App\Models\UjianMurid;
use App\Models\LogPelanggaran;
use App\Models\LogBackupDb;

class AdminDashboardController extends Controller
{
    /**
     * Ringkasan KPI & Analitik Real-Time Dashboard Utama Admin (100% dari Database)
     * GET /api/admin/dashboard
     */
    public function index(): JsonResponse
    {
        $totalMurid = Murid::count();
        $totalGuru = Guru::count();
        $sesiAktif = SesiUjian::where('status_sesi', 'terbuka')->count();
        $totalPelanggaran = LogPelanggaran::count();
        $sesiTerkunci = SesiLoginMurid::where('is_login_locked', true)->count();

        // 1. STATUS PESERTA (DARI DATABASE UJIAN_MURID & MURID)
        $statusSelesaiList = ['selesai_mandiri', 'auto_submit_waktu', 'auto_submit_pelanggaran', 'diskualifikasi'];
        $countSelesai = UjianMurid::whereIn('status_ujian', $statusSelesaiList)->count();
        $countBerlangsung = UjianMurid::where('status_ujian', 'mengerjakan')->count();

        // Murid yang belum mulai ujian sama sekali
        $muridSudahUjianIds = UjianMurid::distinct('id_murid')->pluck('id_murid');
        $countBelumMulai = max(0, $totalMurid - $muridSudahUjianIds->count());

        $totalPartisipasi = max(1, $countSelesai + $countBerlangsung + $countBelumMulai);
        $pctSelesai = round(($countSelesai / $totalPartisipasi) * 100);
        $pctBerlangsung = round(($countBerlangsung / $totalPartisipasi) * 100);
        $pctBelum = max(0, 100 - $pctSelesai - $pctBerlangsung);

        // 2. DISTRIBUSI PESERTA PER JURUSAN (DARI DATABASE MASTER_JURUSAN, KELAS, & MURID)
        $distribusiJurusan = MasterJurusan::with(['kelas.murid'])->get()->map(function ($jurusan) {
            $muridIds = $jurusan->kelas->flatMap(fn($k) => $k->murid->pluck('id_murid'));
            $totalMuridJurusan = $muridIds->count();
            $sudahUjianJurusan = $totalMuridJurusan > 0
                ? UjianMurid::whereIn('id_murid', $muridIds)->distinct('id_murid')->count('id_murid')
                : 0;

            $persentase = $totalMuridJurusan > 0
                ? round(($sudahUjianJurusan / $totalMuridJurusan) * 100)
                : 0;

            return [
                'kode_jurusan' => $jurusan->kode_jurusan,
                'nama_jurusan' => $jurusan->nama_jurusan,
                'total_murid'  => $totalMuridJurusan,
                'sudah_ujian'  => $sudahUjianJurusan,
                'persentase'   => $persentase,
            ];
        });

        // 3. AKTIVITAS TERBARU (GABUNGAN REAL-TIME DARI UJIAN, PELANGGARAN, & LOGIN)
        $aktivitasTerbaru = collect();

        UjianMurid::with(['murid.kelas', 'sesiUjian.plotting.mapel'])->latest('updated_at')->take(4)->get()
            ->each(function ($u) use ($aktivitasTerbaru) {
                $isSelesai = in_array($u->status_ujian, ['selesai_mandiri', 'auto_submit_waktu', 'auto_submit_pelanggaran', 'diskualifikasi']);
                $aktivitasTerbaru->push([
                    'tipe'      => $isSelesai ? 'selesai' : 'mulai',
                    'warna'     => $isSelesai ? 'green' : 'blue',
                    'judul'     => $isSelesai
                        ? "Peserta selesai ujian ({$u->status_ujian})"
                        : "Peserta mengerjakan ujian",
                    'subjudul'  => ($u->murid?->nama_lengkap ?? 'Murid') . ' • ' . ($u->murid?->kelas?->nama_kelas ?? '-') . ' (' . ($u->sesiUjian?->plotting?->mapel?->nama_mapel ?? 'Mapel') . ')',
                    'waktu'     => $u->updated_at ? $u->updated_at->format('H:i') : now()->format('H:i'),
                    'timestamp' => $u->updated_at ? $u->updated_at->timestamp : 0,
                ]);
            });

        LogPelanggaran::with(['ujianMurid.murid.kelas'])->latest('waktu_pelanggaran')->take(4)->get()
            ->each(function ($log) use ($aktivitasTerbaru) {
                $aktivitasTerbaru->push([
                    'tipe'      => 'pelanggaran',
                    'warna'     => 'red',
                    'judul'     => 'Anti-cheat alert: ' . $log->jenis_pelanggaran,
                    'subjudul'  => ($log->ujianMurid?->murid?->nama_lengkap ?? 'Peserta') . ' • ' . ($log->ujianMurid?->murid?->kelas?->nama_kelas ?? '-'),
                    'waktu'     => $log->waktu_pelanggaran ? $log->waktu_pelanggaran->format('H:i') : now()->format('H:i'),
                    'timestamp' => $log->waktu_pelanggaran ? $log->waktu_pelanggaran->timestamp : 0,
                ]);
            });

        $aktivitasSorted = $aktivitasTerbaru->sortByDesc('timestamp')->values()->take(5);

        // 4. GRAFIK AKTIVITAS UJIAN PER JAM (HARI INI VS KEMARIN DARI DATABASE)
        $jamLabels = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
        $jamAngka  = [7, 8, 9, 10, 11, 12, 13, 14, 15, 16];

        $hariIniData = [];
        $kemarinData = [];

        foreach ($jamAngka as $jam) {
            $cHariIni = UjianMurid::whereDate('waktu_mulai', today())
                ->whereRaw('HOUR(waktu_mulai) = ?', [$jam])
                ->count();

            $cKemarin = UjianMurid::whereDate('waktu_mulai', today()->subDay())
                ->whereRaw('HOUR(waktu_mulai) = ?', [$jam])
                ->count();

            $hariIniData[] = $cHariIni;
            $kemarinData[] = $cKemarin;
        }

        return response()->json([
            'status' => 'success',
            'waktu_server' => now()->format('H:i') . ' WIB',
            'kpi'    => [
                'total_murid'           => $totalMurid,
                'total_guru'            => $totalGuru,
                'total_plotting'        => PlottingMapel::count(),
                'sesi_ujian_aktif'      => $sesiAktif,
                'murid_mengerjakan'     => $countBerlangsung,
                'sesi_login_terkunci'   => $sesiTerkunci,
                'total_log_pelanggaran' => $totalPelanggaran,
            ],
            'status_peserta' => [
                'selesai'         => $countSelesai,
                'berlangsung'     => $countBerlangsung,
                'belum_mulai'     => $countBelumMulai,
                'pct_selesai'     => $pctSelesai,
                'pct_berlangsung' => $pctBerlangsung,
                'pct_belum'       => $pctBelum,
            ],
            'distribusi_jurusan' => $distribusiJurusan,
            'aktivitas_terbaru'  => $aktivitasSorted,
            'grafik_per_jam'     => [
                'labels'   => $jamLabels,
                'hari_ini' => $hariIniData,
                'kemarin'  => $kemarinData,
            ],
        ]);
    }

    /**
     * Lihat & Perbarui Profil Akun Admin (Username & Password)
     * GET & PUT /api/admin/profil-akun
     */
    public function showAdminProfile(Request $request): JsonResponse
    {
        $admin = $request->user();
        return response()->json([
            'status' => 'success',
            'data'   => [
                'id_admin'       => $admin->id_admin,
                'username'       => $admin->username,
                'login_terakhir' => $admin->login_terakhir?->format('d-m-Y H:i:s'),
            ],
        ]);
    }

    public function updateAdminProfile(Request $request): JsonResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'username' => "required|string|max:50|unique:admin,username,{$admin->id_admin},id_admin",
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = ['username' => trim($validated['username'])];
        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        $admin->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil & kredensial Administrator berhasil diperbarui.',
            'data'    => [
                'id_admin' => $admin->id_admin,
                'username' => $admin->username,
            ],
        ]);
    }

    /**
     * Saklar Massal (Bulk Toggle) Sesi Ujian Berdasarkan Jurusan / Kelas
     * POST /api/admin/sesi-ujian/bulk-toggle
     */
    public function bulkToggleSesi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_jurusan'       => 'nullable|string',
            'id_kelas'           => 'nullable|integer',
            'status_sesi'        => 'sometimes|in:terbuka,tertutup',
            'visibilitas_jadwal' => 'sometimes|in:tampil,tutup',
            'publikasi_nilai'    => 'sometimes|boolean',
        ]);

        $query = SesiUjian::query();

        if (!empty($validated['id_kelas'])) {
            $query->whereHas('plotting', fn($q) => $q->where('id_kelas', $validated['id_kelas']));
        } elseif (!empty($validated['kode_jurusan'])) {
            $kode = $validated['kode_jurusan'];
            $query->whereHas('plotting.kelas.jurusan', fn($q) => $q->where('kode_jurusan', $kode));
        }

        $updateFields = collect($validated)->only(['status_sesi', 'visibilitas_jadwal', 'publikasi_nilai'])->toArray();
        $affected = $query->update($updateFields);

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil memperbarui {$affected} sesi ujian sekaligus.",
        ]);
    }

    public function listLockedSessions(Request $request): JsonResponse
    {
        $query = SesiLoginMurid::with(['murid.kelas.jurusan', 'murid.ujianMurid' => function ($q) {
            $q->where('status_ujian', 'mengerjakan');
        }])->where('is_login_locked', true);

        return response()->json([
            'status' => 'success',
            'data'   => $query->latest('waktu_login')->get(),
        ]);
    }

    public function unlockStudentSession(int $id_murid): JsonResponse
    {
        $murid = Murid::findOrFail($id_murid);

        SesiLoginMurid::updateOrCreate(
            ['id_murid' => $murid->id_murid],
            ['session_token' => null, 'is_login_locked' => false]
        );

        $murid->tokens()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Sesi login murid {$murid->nama_lengkap} (NIS: {$murid->nis}) berhasil dibuka (Unlocked). Progres ujian tetap aman.",
        ]);
    }

    /**
     * Live Monitoring Lengkap (Termasuk Relasi Jurusan, Kelas, & Mapel)
     * GET /api/admin/live-monitoring
     */
    public function liveMonitoring(Request $request): JsonResponse
    {
        $peserta = UjianMurid::with([
            'murid.kelas.jurusan',
            'murid.sesiLogin',
            'sesiUjian.plotting.mapel',
            'sesiUjian.plotting.guru',
            'hasilNilai',
        ])->withCount('daftarJawaban')->latest('updated_at')->get();

        $feedPelanggaran = LogPelanggaran::with([
            'ujianMurid.murid.kelas.jurusan',
            'ujianMurid.sesiUjian.plotting.mapel',
        ])->latest('waktu_pelanggaran')->take(30)->get();

        return response()->json([
            'status'           => 'success',
            'peserta'          => $peserta,
            'feed_pelanggaran' => $feedPelanggaran,
        ]);
    }

    public function forceCloseExam(int $id_ujian_murid): JsonResponse
    {
        $ujianMurid = UjianMurid::with('murid')->findOrFail($id_ujian_murid);

        $ujianMurid->update([
            'status_ujian'     => 'diskualifikasi',
            'sisa_waktu_detik' => 0,
            'fullscreen_lock'  => false,
        ]);

        LogPelanggaran::create([
            'id_ujian_murid'    => $ujianMurid->id_ujian_murid,
            'jenis_pelanggaran' => 'Dihentikan Paksa (Force Close / Diskualifikasi) oleh Administrator',
            'waktu_pelanggaran' => now(),
        ]);

        return response()->json([
            'status'      => 'success',
            'message'     => "Sesi ujian murid {$ujianMurid->murid->nama_lengkap} telah dihentikan paksa (Diskualifikasi).",
            'hasil_nilai' => $ujianMurid->fresh()->hasilNilai,
        ]);
    }

    public function backupDatabase(Request $request): JsonResponse
    {
        $admin = $request->user();
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = "Tables_in_{$dbName}";

        $sqlDump = "-- BACKUP DATABASE CBT SMK NEGERI 1 KOTA BEKASI (" . now()->toDateTimeString() . ")\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$keyName;
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n" . $createTable[0]->{'Create Table'} . ";\n\n";
        }
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $fileName = 'backup_cbt_smkn1bks_' . now()->format('Ymd_His') . '.sql';
        $sizeKb = round(strlen($sqlDump) / 1024, 2);

        LogBackupDb::create([
            'id_admin'       => $admin->id_admin,
            'nama_file_sql'  => $fileName,
            'ukuran_file_kb' => $sizeKb,
            'waktu_backup'   => now(),
        ]);

        return response()->json([
            'status'         => 'success',
            'nama_file_sql'  => $fileName,
            'ukuran_file_kb' => $sizeKb,
            'sql_content'    => $sqlDump,
        ]);
    }
}