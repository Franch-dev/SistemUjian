<?php

namespace App\Observers;

use App\Models\UjianMurid;
use App\Models\HasilNilaiUjian;

class UjianMuridObserver
{
    /**
     * Sebelum disimpan: Cek apakah pelanggaran sudah mencapai batas toleransi anti-cheat.
     */
    public function saving(UjianMurid $ujianMurid): void
    {
        $sesi = $ujianMurid->sesiUjian;

        if ($sesi && $ujianMurid->status_ujian === 'mengerjakan') {
            if ($ujianMurid->jumlah_pelanggaran >= $sesi->batas_toleransi_anticheat) {
                $ujianMurid->status_ujian = 'auto_submit_pelanggaran';
                $ujianMurid->sisa_waktu_detik = 0;
                $ujianMurid->fullscreen_lock = false;
            }
        }
    }

    /**
     * Setelah disimpan: Jika status ujian berubah menjadi selesai/diskualifikasi,
     * hitung otomatis Nilai Akhir dan Status Kelulusan (Lulus / Remedial) ke tabel hasil_nilai_ujian.
     */
    public function saved(UjianMurid $ujianMurid): void
    {
        $statusSelesai = [
            'selesai_mandiri',
            'auto_submit_waktu',
            'auto_submit_pelanggaran',
            'diskualifikasi',
        ];

        if (in_array($ujianMurid->status_ujian, $statusSelesai, true)) {
            $this->hitungDanSimpanNilai($ujianMurid);
        }
    }

    protected function hitungDanSimpanNilai(UjianMurid $ujianMurid): void
    {
        $plotting = $ujianMurid->sesiUjian?->plotting;
        if (!$plotting) {
            return;
        }

        $daftarSoal = $plotting->bankSoal()->get();
        $jawabanMurid = $ujianMurid->daftarJawaban()->get()->keyBy('id_soal');

        $totalBobotMaksimal = $daftarSoal->sum('bobot_soal');
        $bobotDiperoleh = 0;
        $jumlahBenar = 0;

        foreach ($daftarSoal as $soal) {
            $jawaban = $jawabanMurid->get($soal->id_soal);
            if ($jawaban && $jawaban->pilihan_jawaban === $soal->kunci_jawaban) {
                $jumlahBenar++;
                $bobotDiperoleh += $soal->bobot_soal;
            }
        }

        $nilaiAkhir = $totalBobotMaksimal > 0
            ? round(($bobotDiperoleh / $totalBobotMaksimal) * 100, 2)
            : 0;

        $nilaiKkm = $plotting->mapel?->nilai_kkm ?? 75;
        $statusKelulusan = ($nilaiAkhir >= $nilaiKkm && $ujianMurid->status_ujian !== 'diskualifikasi')
            ? 'Lulus'
            : 'Remedial';

        HasilNilaiUjian::updateOrCreate(
            ['id_ujian_murid' => $ujianMurid->id_ujian_murid],
            [
                'jumlah_benar' => $jumlahBenar,
                'nilai_akhir' => $nilaiAkhir,
                'status_kelulusan' => $statusKelulusan,
                'waktu_selesai' => now(),
            ]
        );
    }
}