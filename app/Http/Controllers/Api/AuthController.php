<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\Guru;
use App\Models\Murid;
use App\Models\SesiLoginMurid;
use App\Models\UjianMurid;

class AuthController extends Controller
{
    /**
     * Endpoint Unified Login untuk 3 Role: Admin, Guru, Murid
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'role'       => 'required|in:admin,guru,murid',
            'identifier' => 'required|string', // username (admin), kode_guru/nip (guru), nama_lengkap/nis (murid)
            'password'   => 'required|string',
        ]);

        $role = $request->role;
        $identifier = trim($request->identifier);
        $password = $request->password;

        switch ($role) {
            case 'admin':
                return $this->loginAdmin($identifier, $password);
            case 'guru':
                return $this->loginGuru($identifier, $password);
            case 'murid':
                return $this->loginMurid($identifier, $password);
            default:
                return response()->json(['message' => 'Role tidak valid.'], 400);
        }
    }

    protected function loginAdmin(string $identifier, string $password): JsonResponse
    {
        $admin = Admin::where('username', $identifier)->first();

        if (!$admin || !Hash::check($password, $admin->password_hash)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kredensial Admin salah atau akun tidak ditemukan.',
            ], 401);
        }

        $admin->update(['login_terakhir' => now()]);
        $token = $admin->createToken('admin-token', ['role:admin'])->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login Admin berhasil.',
            'role'    => 'admin',
            'token'   => $token,
            'user'    => [
                'id_admin'       => $admin->id_admin,
                'username'       => $admin->username,
                'login_terakhir' => $admin->login_terakhir,
            ],
        ]);
    }

    protected function loginGuru(string $identifier, string $password): JsonResponse
    {
        $guru = Guru::where('kode_guru', $identifier)
                    ->orWhere('nip', $identifier)
                    ->first();

        if (!$guru || !Hash::check($password, $guru->password_hash)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode Guru / NIP atau password salah.',
            ], 401);
        }

        $token = $guru->createToken('guru-token', ['role:guru'])->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login Guru berhasil.',
            'role'    => 'guru',
            'token'   => $token,
            'user'    => [
                'id_guru'      => $guru->id_guru,
                'kode_guru'    => $guru->kode_guru,
                'nama_lengkap' => $guru->nama_lengkap,
                'nip'          => $guru->nip,
            ],
        ]);
    }

    protected function loginMurid(string $identifier, string $password): JsonResponse
    {
        // Murid bisa login dengan Nama Lengkap atau NIS
        $murid = Murid::with(['kelas.jurusan', 'sesiLogin'])
                      ->where('nis', $identifier)
                      ->orWhere('nama_lengkap', $identifier)
                      ->first();

        if (!$murid || !Hash::check($password, $murid->password_hash)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'NIS / Nama Murid atau password salah.',
            ], 401);
        }

        // VALIDASI SINGLE DEVICE LOCK
        $sesiLogin = $murid->sesiLogin;
        if ($sesiLogin && $sesiLogin->is_login_locked) {
            return response()->json([
                'status'       => 'locked',
                'error_code'   => 'ERR_SINGLE_DEVICE_LOCK',
                'message'      => 'Akses Ditolak: Akun Anda sedang aktif di perangkat lain. Silakan hubungi Admin / Pengawas Ruang untuk melakukan Reset Session.',
                'waktu_login'  => $sesiLogin->waktu_login,
            ], 403);
        }

        // Generate session token baru & kunci sesi perangkat
        $sessionTokenString = Str::uuid()->toString();

        SesiLoginMurid::updateOrCreate(
            ['id_murid' => $murid->id_murid],
            [
                'session_token'   => $sessionTokenString,
                'is_login_locked' => true, // Kunci ke perangkat ini
                'waktu_login'     => now(),
            ]
        );

        $sanctumToken = $murid->createToken('murid-token', ['role:murid'])->plainTextToken;

        // Cek apakah ada ujian yang sedang aktif/berjalan (Resume Exam Feature)
        $ujianAktif = UjianMurid::where('id_murid', $murid->id_murid)
                                ->where('status_ujian', 'mengerjakan')
                                ->with('sesiUjian.plotting.mapel')
                                ->first();

        return response()->json([
            'status'       => 'success',
            'message'      => 'Login Murid berhasil. Perangkat dikunci (Single Device Lock aktif).',
            'role'         => 'murid',
            'token'        => $sanctumToken,
            'device_token' => $sessionTokenString,
            'user'         => [
                'id_murid'     => $murid->id_murid,
                'nis'          => $murid->nis,
                'nama_lengkap' => $murid->nama_lengkap,
                'kelas'        => $murid->kelas?->nama_kelas,
                'jurusan'      => $murid->kelas?->jurusan?->nama_jurusan,
            ],
            // Data untuk Resume Ujian otomatis jika sebelumnya terjadi error/reset session
            'resume_exam'  => $ujianAktif ? [
                'can_resume'       => true,
                'id_ujian_murid'   => $ujianAktif->id_ujian_murid,
                'nama_sesi'        => $ujianAktif->sesiUjian?->nama_sesi_ujian,
                'nama_mapel'       => $ujianAktif->sesiUjian?->plotting?->mapel?->nama_mapel,
                'sisa_waktu_detik' => $ujianAktif->sisa_waktu_detik,
                'pelanggaran'      => $ujianAktif->jumlah_pelanggaran,
            ] : null,
        ]);
    }

    /**
     * Endpoint Logout (Menghapus Token Sanctum & Membuka Kunci Single Device Lock)
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof Murid) {
            // Lepas kunci Single Device Lock saat murid logout secara resmi
            SesiLoginMurid::where('id_murid', $user->id_murid)->update([
                'is_login_locked' => false,
                'session_token'   => null,
            ]);
        }

        // Hapus token akses saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil. Sesi login telah diakhiri.',
        ]);
    }

    /**
     * Endpoint Cek User Sedang Login
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'user'   => $request->user(),
        ]);
    }
}