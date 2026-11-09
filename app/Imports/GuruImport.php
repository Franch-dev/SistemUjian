<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToCollection, WithHeadingRow
{
    public int $importedCount = 0;

    /**
     * Header kolom di Excel/CSV: nama_lengkap | nip | kode_guru (opsional) | password (opsional)
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['nama_lengkap'])) {
                continue;
            }

            $rawPassword = !empty($row['password']) ? trim((string) $row['password']) : 'guru123456';
            $nip = !empty($row['nip']) ? trim((string) $row['nip']) : null;

            $guru = new Guru([
                'kode_guru'     => !empty($row['kode_guru']) ? trim((string) $row['kode_guru']) : null,
                'nama_lengkap'  => trim((string) $row['nama_lengkap']),
                'nip'           => $nip,
                'password_hash' => Hash::make($rawPassword),
            ]);
            $guru->save();

            $this->importedCount++;
        }
    }
}