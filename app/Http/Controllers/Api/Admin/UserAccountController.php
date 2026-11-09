<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Guru;
use App\Models\Murid;
use App\Models\SesiLoginMurid;

class UserAccountController extends Controller
{
    // =========================================================================
    // KELOLA AKUN GURU
    // =========================================================================
    public function indexGuru(Request $request): JsonResponse
    {
        $query = Guru::withCount('plottingMapel');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('kode_guru', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%");
        }
        return response()->json(['data' => $query->latest()->get()]);
    }

    public function storeGuru(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nip'          => 'nullable|string|max:30',
            'password'     => 'nullable|string|min:6',
        ]);

        $rawPassword = $validated['password'] ?? Str::upper(Str::random(8));

        // kode_guru otomatis di-generate oleh Model Guru::booted() (6 huruf nama + 4 angka)
        $guru = Guru::create([
            'nama_lengkap'  => $validated['nama_lengkap'],
            'nip'           => $validated['nip'] ?? null,
            'password_hash' => Hash::make($rawPassword),
        ]);

        return response()->json([
            'message'            => 'Akun Guru berhasil dibuat.',
            'data'               => $guru,
            'generated_password' => $rawPassword,
        ], 201);
    }

    public function updateGuru(Request $request, int $id): JsonResponse
    {
        $guru = Guru::findOrFail($id);
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nip'          => 'nullable|string|max:30',
            'password'     => 'nullable|string|min:6',
        ]);

        $updateData = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'nip'          => $validated['nip'] ?? $guru->nip,
        ];

        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        $guru->update($updateData);
        return response()->json(['message' => 'Akun Guru berhasil diperbarui.', 'data' => $guru]);
    }

    public function destroyGuru(int $id): JsonResponse
        {
            Guru::findOrFail($id)->delete();

            if (Guru::count() === 0) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE guru AUTO_INCREMENT = 1');
            }

            return response()->json(['message' => 'Akun Guru berhasil dihapus.']);
        }

    // =========================================================================
    // KELOLA AKUN MURID
    // =========================================================================
    public function indexMurid(Request $request): JsonResponse
    {
        $query = Murid::with(['kelas.jurusan', 'sesiLogin']);

        if ($request->filled('id_kelas')) {
            $query->where('id_kelas', $request->id_kelas);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('nis', 'like', "%{$s}%");
            });
        }

        return response()->json(['data' => $query->latest()->get()]);
    }

    public function storeMurid(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_kelas'     => 'required|exists:master_kelas,id_kelas',
            'nis'          => 'required|string|max:30|unique:murid,nis',
            'nama_lengkap' => 'required|string|max:150',
            'password'     => 'nullable|string|min:6',
        ]);

        $rawPassword = $validated['password'] ?? $validated['nis'];

        $murid = Murid::create([
            'id_kelas'       => $validated['id_kelas'],
            'nis'            => $validated['nis'],
            'nama_lengkap'   => $validated['nama_lengkap'],
            'password_hash'  => Hash::make($rawPassword),
            'password_cetak' => $rawPassword,
        ]);

        // Buat record sesi_login_murid default (terbuka)
        SesiLoginMurid::create([
            'id_murid'        => $murid->id_murid,
            'is_login_locked' => false,
        ]);

        return response()->json([
            'message' => 'Akun Murid berhasil ditambahkan.',
            'data'    => $murid->load('kelas'),
        ], 201);
    }

    public function updateMurid(Request $request, int $id): JsonResponse
    {
        $murid = Murid::findOrFail($id);
        $validated = $request->validate([
            'id_kelas'     => 'required|exists:master_kelas,id_kelas',
            'nis'          => "required|string|max:30|unique:murid,nis,{$id},id_murid",
            'nama_lengkap' => 'required|string|max:150',
            'password'     => 'nullable|string|min:6',
        ]);

        $updateData = [
            'id_kelas'     => $validated['id_kelas'],
            'nis'          => $validated['nis'],
            'nama_lengkap' => $validated['nama_lengkap'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password_hash']  = Hash::make($validated['password']);
            $updateData['password_cetak'] = $validated['password'];
        }

        $murid->update($updateData);
        return response()->json(['message' => 'Data Murid berhasil diperbarui.', 'data' => $murid]);
    }

    public function destroyMurid(int $id): JsonResponse
        {
            $murid = Murid::findOrFail($id);
            $murid->sesiLogin()->delete();
            $murid->delete();

            if (Murid::count() === 0) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE sesi_login_murid AUTO_INCREMENT = 1');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE murid AUTO_INCREMENT = 1');
            }

            return response()->json(['message' => 'Akun Murid berhasil dihapus.']);
        }
}