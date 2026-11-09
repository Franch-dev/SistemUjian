<?php

namespace App\Exports;

use App\Models\UjianMurid;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapNilaiExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?int $idPlotting;
    protected ?int $idSesi;

    public function __construct(?int $idPlotting = null, ?int $idSesi = null)
    {
        $this->idPlotting = $idPlotting;
        $this->idSesi = $idSesi;
    }

    public function collection(): Collection
    {
        return UjianMurid::with(['murid.kelas', 'sesiUjian.plotting.mapel', 'hasilNilai'])
            ->when($this->idSesi, fn($q) => $q->where('id_sesi', $this->idSesi))
            ->when($this->idPlotting, function ($q) {
                $q->whereHas('sesiUjian', fn($sub) => $sub->where('id_plotting', $this->idPlotting));
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Lengkap Murid',
            'Kelas',
            'Mata Pelajaran',
            'Sesi Ujian',
            'KKM',
            'Jumlah Benar',
            'Nilai Akhir',
            'Status Kelulusan',
            'Status Penyelesaian',
            'Jumlah Pelanggaran Anti-Cheat',
            'Waktu Selesai',
        ];
    }

    public function map($ujian): array
    {
        $mapel = $ujian->sesiUjian?->plotting?->mapel;

        return [
            $ujian->murid?->nis ?? '-',
            $ujian->murid?->nama_lengkap ?? '-',
            $ujian->murid?->kelas?->nama_kelas ?? '-',
            $mapel?->nama_mapel ?? '-',
            $ujian->sesiUjian?->nama_sesi_ujian ?? '-',
            $mapel?->nilai_kkm ?? 75,
            $ujian->hasilNilai?->jumlah_benar ?? 0,
            $ujian->hasilNilai?->nilai_akhir ?? 0,
            $ujian->hasilNilai?->status_kelulusan ?? 'Belum Selesai',
            $ujian->status_ujian,
            $ujian->jumlah_pelanggaran,
            $ujian->hasilNilai?->waktu_selesai?->format('Y-m-d H:i:s') ?? '-',
        ];
    }
}