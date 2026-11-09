<?php

namespace App\Exports;

use App\Models\Murid;
use Illuminate\Support\Collection; // <--- 1. Tambahkan import ini
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MuridExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?int $idKelas;

    public function __construct(?int $idKelas = null)
    {
        $this->idKelas = $idKelas;
    }

    // 2. Tambahkan ": Collection" di akhir deklarasi fungsi ini
    public function collection(): Collection 
    {
        return Murid::with(['kelas.jurusan', 'sesiLogin'])
            ->when($this->idKelas, fn($q) => $q->where('id_kelas', $this->idKelas))
            ->orderBy('nama_lengkap')
            ->get();
    }

    public function headings(): array
    {
        return ['ID Murid', 'NIS', 'Nama Lengkap', 'Kelas', 'Jurusan', 'Password Kartu', 'Status Sesi Login'];
    }

    public function map($murid): array
    {
        return [
            $murid->id_murid,
            $murid->nis,
            $murid->nama_lengkap,
            $murid->kelas?->nama_kelas ?? '-',
            $murid->kelas?->jurusan?->nama_jurusan ?? '-',
            $murid->password_cetak ?? '-',
            ($murid->sesiLogin && $murid->sesiLogin->is_login_locked) ? 'Terkunci (Aktif)' : 'Terbuka',
        ];
    }
}