<?php

namespace App\Exports;

use App\Models\LogPelanggaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LogPelanggaranExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?int $idPlotting;

    public function __construct(?int $idPlotting = null)
    {
        $this->idPlotting = $idPlotting;
    }

    public function collection(): Collection
    {
        return LogPelanggaran::with(['ujianMurid.murid.kelas', 'ujianMurid.sesiUjian.plotting.mapel'])
            ->when($this->idPlotting, function ($q) {
                $q->whereHas('ujianMurid.sesiUjian', fn($sub) => $sub->where('id_plotting', $this->idPlotting));
            })
            ->latest('waktu_pelanggaran')
            ->get();
    }

    public function headings(): array
    {
        return ['ID Log', 'Waktu Kejadian', 'NIS', 'Nama Murid', 'Kelas', 'Mata Pelajaran', 'Jenis Pelanggaran', 'Total Pelanggaran Murid'];
    }

    public function map($log): array
    {
        return [
            $log->id_log_pelanggaran,
            $log->waktu_pelanggaran?->format('Y-m-d H:i:s'),
            $log->ujianMurid?->murid?->nis ?? '-',
            $log->ujianMurid?->murid?->nama_lengkap ?? '-',
            $log->ujianMurid?->murid?->kelas?->nama_kelas ?? '-',
            $log->ujianMurid?->sesiUjian?->plotting?->mapel?->nama_mapel ?? '-',
            $log->jenis_pelanggaran,
            $log->ujianMurid?->jumlah_pelanggaran ?? 0,
        ];
    }
}