<?php

namespace App\Exports;

use App\Models\Guru;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuruExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Guru::withCount('plottingMapel')->orderBy('nama_lengkap')->get();
    }

    public function headings(): array
    {
        return ['ID Guru', 'Kode Guru (Username)', 'Nama Lengkap', 'NIP', 'Jumlah Kelas Ampuan'];
    }

    public function map($guru): array
    {
        return [
            $guru->id_guru,
            $guru->kode_guru,
            $guru->nama_lengkap,
            $guru->nip ?? '-',
            $guru->plotting_mapel_count,
        ];
    }
}