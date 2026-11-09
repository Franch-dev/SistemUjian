<?php

namespace App\Imports;

use App\Models\BankSoal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankSoalImport implements ToCollection, WithHeadingRow
{
    protected int $idPlotting;
    public int $importedCount = 0;

    public function __construct(int $idPlotting)
    {
        $this->idPlotting = $idPlotting;
    }

    /**
     * Header kolom di Excel/CSV:
     * pertanyaan | opsi_a | opsi_b | opsi_c | opsi_d | opsi_e | kunci_jawaban | bobot_soal
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['pertanyaan']) || empty($row['opsi_a'])) {
                continue;
            }

            $kunci = strtoupper(trim((string) ($row['kunci_jawaban'] ?? 'A')));
            if (!in_array($kunci, ['A', 'B', 'C', 'D', 'E'], true)) {
                $kunci = 'A';
            }

            BankSoal::create([
                'id_plotting'   => $this->idPlotting,
                'pertanyaan'    => trim((string) $row['pertanyaan']),
                'path_gambar'   => null,
                'opsi_a'        => trim((string) ($row['opsi_a'] ?? '-')),
                'opsi_b'        => trim((string) ($row['opsi_b'] ?? '-')),
                'opsi_c'        => trim((string) ($row['opsi_c'] ?? '-')),
                'opsi_d'        => trim((string) ($row['opsi_d'] ?? '-')),
                'opsi_e'        => trim((string) ($row['opsi_e'] ?? '-')),
                'kunci_jawaban' => $kunci,
                'bobot_soal'    => (int) ($row['bobot_soal'] ?? 1),
            ]);

            $this->importedCount++;
        }
    }
}