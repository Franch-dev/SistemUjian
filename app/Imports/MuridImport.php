<?php

namespace App\Imports;

use App\Models\Murid;
use App\Models\MasterKelas;
use App\Models\SesiLoginMurid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MuridImport implements ToCollection, WithHeadingRow
{
    public int $importedCount = 0;

    /**
     * Header kolom di Excel/CSV:
     * nis | nama_lengkap | nama_kelas | password (opsional)
     */
    public function collection(Collection $rows): void
    {
        // Jika seluruh data dummy sebelumnya sudah dihapus (tabel kosong),
        // otomatis reset nomor urut id_murid kembali mulai dari 1
        if (Murid::count() === 0) {
            DB::statement('ALTER TABLE sesi_login_murid AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE murid AUTO_INCREMENT = 1');
        }

        foreach ($rows as $row) {
            if (empty($row['nis']) || empty($row['nama_lengkap'])) {
                continue;
            }

            $inputKelas = $row['nama_kelas'] ?? $row['kelas'] ?? 'XII RPL 1';
            $namaKelas = trim((string) $inputKelas);

            $kelas = MasterKelas::where('nama_kelas', $namaKelas)->first()
                     ?? MasterKelas::first();

            if (!$kelas) {
                continue;
            }

            $nis = trim((string) $row['nis']);
            $inputPass = $row['password'] ?? $row['password_kartu'] ?? '';
            $rawPassword = (!empty($inputPass) && $inputPass !== '-') ? trim((string) $inputPass) : $nis;

            $murid = Murid::updateOrCreate(
                ['nis' => $nis],
                [
                    'id_kelas'       => $kelas->id_kelas,
                    'nama_lengkap'   => trim((string) $row['nama_lengkap']),
                    'password_hash'  => Hash::make($rawPassword),
                    'password_cetak' => $rawPassword,
                ]
            );

            SesiLoginMurid::firstOrCreate(
                ['id_murid' => $murid->id_murid],
                ['is_login_locked' => false, 'session_token' => null]
            );

            $this->importedCount++;
        }
    }
}