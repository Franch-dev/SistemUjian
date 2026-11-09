<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use App\Imports\MuridImport;
use App\Imports\GuruImport;
use App\Imports\BankSoalImport;
use App\Exports\MuridExport;
use App\Exports\GuruExport;
use App\Exports\RekapNilaiExport;
use App\Exports\LogPelanggaranExport;
use App\Models\ProfilSekolah;
use App\Models\Murid;
use App\Models\SesiUjian;
use App\Models\UjianMurid;
use App\Models\PlottingMapel;
use App\Models\BankSoal;

class DocumentController extends Controller
{
    // =========================================================================
    // 1. IMPORT & EXPORT DATA AKUN MURID & GURU (ADMIN)
    // =========================================================================
    public function importMurid(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new MuridImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'status'         => 'success',
            'message'        => "Berhasil mengimpor {$import->importedCount} data akun murid.",
            'imported_count' => $import->importedCount,
        ]);
    }

    public function exportMurid(Request $request)
    {
        $format = $request->query('format', 'xlsx'); // 'xlsx' atau 'csv'
        $idKelas = $request->query('id_kelas');
        $fileName = 'data_murid_smkn1bekasi_' . now()->format('Ymd_His') . '.' . $format;

        return Excel::download(new MuridExport($idKelas ? (int) $idKelas : null), $fileName);
    }

    public function importGuru(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new GuruImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'status'         => 'success',
            'message'        => "Berhasil mengimpor {$import->importedCount} data akun guru.",
            'imported_count' => $import->importedCount,
        ]);
    }

    public function exportGuru(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        $fileName = 'data_guru_smkn1bekasi_' . now()->format('Ymd_His') . '.' . $format;

        return Excel::download(new GuruExport(), $fileName);
    }

    // =========================================================================
    // 2. IMPORT BANK SOAL VIA EXCEL/CSV & MICROSOFT WORD (.DOCX) (GURU & ADMIN)
    // =========================================================================
    public function importBankSoalExcel(Request $request, int $id_plotting): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $plotting = PlottingMapel::findOrFail($id_plotting);
        $import = new BankSoalImport($plotting->id_plotting);
        Excel::import($import, $request->file('file'));

        return response()->json([
            'status'         => 'success',
            'message'        => "Berhasil mengimpor {$import->importedCount} butir soal pilihan ganda dari Excel/CSV.",
            'imported_count' => $import->importedCount,
        ]);
    }

    /**
     * Import Bank Soal dari Tabel di dalam Dokumen Microsoft Word (.docx)
     * Format Kolom Tabel di Word:
     * Kolom 0: Pertanyaan | Kolom 1: Opsi A | Kolom 2: Opsi B | Kolom 3: Opsi C | Kolom 4: Opsi D | Kolom 5: Opsi E | Kolom 6: Kunci (A-E) | Kolom 7: Bobot
     */
    public function importBankSoalWord(Request $request, int $id_plotting): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:docx|max:5120',
        ]);

        $plotting = PlottingMapel::findOrFail($id_plotting);
        $phpWord = WordIOFactory::load($request->file('file')->getPathname());
        $importedCount = 0;

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getRows')) {
                    $rows = $element->getRows();
                    foreach ($rows as $rowIndex => $row) {
                        // Lewati baris pertama (Header Tabel)
                        if ($rowIndex === 0) {
                            continue;
                        }

                        $cells = $row->getCells();
                        if (count($cells) < 7) {
                            continue;
                        }

                        $extractCellText = function ($cell): string {
                            $text = '';
                            foreach ($cell->getElements() as $cellElement) {
                                if (method_exists($cellElement, 'getText')) {
                                    $text .= $cellElement->getText() . ' ';
                                }
                            }
                            return trim($text);
                        };

                        $pertanyaan = $extractCellText($cells[0]);
                        if (empty($pertanyaan)) {
                            continue;
                        }

                        $kunci = strtoupper(substr($extractCellText($cells[6]), 0, 1));
                        if (!in_array($kunci, ['A', 'B', 'C', 'D', 'E'], true)) {
                            $kunci = 'A';
                        }

                        $bobot = isset($cells[7]) ? (int) $extractCellText($cells[7]) : 1;

                        BankSoal::create([
                            'id_plotting'   => $plotting->id_plotting,
                            'pertanyaan'    => $pertanyaan,
                            'path_gambar'   => null,
                            'opsi_a'        => $extractCellText($cells[1]) ?: '-',
                            'opsi_b'        => $extractCellText($cells[2]) ?: '-',
                            'opsi_c'        => $extractCellText($cells[3]) ?: '-',
                            'opsi_d'        => $extractCellText($cells[4]) ?: '-',
                            'opsi_e'        => $extractCellText($cells[5]) ?: '-',
                            'kunci_jawaban' => $kunci,
                            'bobot_soal'    => $bobot > 0 ? $bobot : 1,
                        ]);

                        $importedCount++;
                    }
                }
            }
        }

        return response()->json([
            'status'         => 'success',
            'message'        => "Berhasil mengimpor {$importedCount} butir soal dari dokumen Word (.docx).",
            'imported_count' => $importedCount,
        ]);
    }

    // =========================================================================
    // 3. EXPORT REKAP NILAI & LOG PELANGGARAN (EXCEL/CSV)
    // =========================================================================
    public function exportRekapNilai(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        $idPlotting = $request->query('id_plotting');
        $idSesi = $request->query('id_sesi');
        $fileName = 'rekap_nilai_cbt_' . now()->format('Ymd_His') . '.' . $format;

        return Excel::download(
            new RekapNilaiExport(
                $idPlotting ? (int) $idPlotting : null,
                $idSesi ? (int) $idSesi : null
            ),
            $fileName
        );
    }

    public function exportLogPelanggaran(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        $idPlotting = $request->query('id_plotting');
        $fileName = 'log_audit_anticheat_' . now()->format('Ymd_His') . '.' . $format;

        return Excel::download(
            new LogPelanggaranExport($idPlotting ? (int) $idPlotting : null),
            $fileName
        );
    }

    // =========================================================================
    // 4. CETAK BERKAS PDF (KARTU PESERTA UJIAN & DAFTAR HADIR / BERITA ACARA)
    // =========================================================================
    public function cetakKartuPesertaPdf(Request $request)
    {
        $profil = ProfilSekolah::first();
        $idKelas = $request->query('id_kelas');

        $daftarMurid = Murid::with('kelas.jurusan')
            ->when($idKelas, fn($q) => $q->where('id_kelas', $idKelas))
            ->orderBy('nama_lengkap')
            ->get();

        $pdf = Pdf::loadView('pdf.kartu_peserta', [
            'profil'      => $profil,
            'daftarMurid' => $daftarMurid,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('kartu_peserta_ujian_smkn1bekasi.pdf');
    }

    public function cetakDaftarHadirPdf(int $id_sesi)
    {
        $profil = ProfilSekolah::first();
        $sesi = SesiUjian::with(['plotting.mapel', 'plotting.kelas', 'plotting.guru'])->findOrFail($id_sesi);

        $semuaMuridKelas = Murid::where('id_kelas', $sesi->plotting->id_kelas)
            ->orderBy('nama_lengkap')
            ->get();

        $ujianPerMurid = UjianMurid::where('id_sesi', $sesi->id_sesi)
            ->get()
            ->keyBy('id_murid');

        $pdf = Pdf::loadView('pdf.daftar_hadir_berita_acara', [
            'profil'          => $profil,
            'sesi'            => $sesi,
            'semuaMuridKelas' => $semuaMuridKelas,
            'ujianPerMurid'   => $ujianPerMurid,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("daftar_hadir_berita_acara_sesi_{$id_sesi}.pdf");
    }
}