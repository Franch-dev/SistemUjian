<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\MasterAkademikController;
use App\Http\Controllers\Api\Admin\UserAccountController;
use App\Http\Controllers\Api\Guru\GuruCbtController;
use App\Http\Controllers\Api\Murid\UjianMuridController;
use App\Http\Controllers\Api\DocumentController;

/*
|--------------------------------------------------------------------------
| 1. ROUTE AUTENTIKASI (LOGIN & LOGOUT MULTI-ROLE)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| 2. ROUTE ROLE ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

    // Dashboard, Profil Admin, Reset Session, Live Monitoring, Force Close, & Backup DB
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/profil-akun', [AdminDashboardController::class, 'showAdminProfile']);
    Route::put('/profil-akun', [AdminDashboardController::class, 'updateAdminProfile']);
    Route::get('/reset-sessions', [AdminDashboardController::class, 'listLockedSessions']);
    Route::post('/reset-sessions/{id_murid}/unlock', [AdminDashboardController::class, 'unlockStudentSession']);
    Route::get('/live-monitoring', [AdminDashboardController::class, 'liveMonitoring']);
    Route::post('/live-monitoring/{id_ujian_murid}/force-close', [AdminDashboardController::class, 'forceCloseExam']);
    Route::post('/backup-database', [AdminDashboardController::class, 'backupDatabase']);

    // Kelola Akun Guru & Murid
    Route::get('/guru', [UserAccountController::class, 'indexGuru']);
    Route::post('/guru', [UserAccountController::class, 'storeGuru']);
    Route::put('/guru/{id}', [UserAccountController::class, 'updateGuru']);
    Route::delete('/guru/{id}', [UserAccountController::class, 'destroyGuru']);

    Route::get('/murid', [UserAccountController::class, 'indexMurid']);
    Route::post('/murid', [UserAccountController::class, 'storeMurid']);
    Route::put('/murid/{id}', [UserAccountController::class, 'updateMurid']);
    Route::delete('/murid/{id}', [UserAccountController::class, 'destroyMurid']);

    // Master Jurusan, Kelas, Mapel, Plotting, & Profil Sekolah
    Route::get('/jurusan', [MasterAkademikController::class, 'indexJurusan']);
    Route::post('/jurusan', [MasterAkademikController::class, 'storeJurusan']);
    Route::put('/jurusan/{id}', [MasterAkademikController::class, 'updateJurusan']);
    Route::delete('/jurusan/{id}', [MasterAkademikController::class, 'destroyJurusan']);

    Route::get('/kelas', [MasterAkademikController::class, 'indexKelas']);
    Route::post('/kelas', [MasterAkademikController::class, 'storeKelas']);
    Route::put('/kelas/{id}', [MasterAkademikController::class, 'updateKelas']);
    Route::delete('/kelas/{id}', [MasterAkademikController::class, 'destroyKelas']);

    Route::get('/mapel', [MasterAkademikController::class, 'indexMapel']);
    Route::post('/mapel', [MasterAkademikController::class, 'storeMapel']);
    Route::put('/mapel/{id}', [MasterAkademikController::class, 'updateMapel']);
    Route::delete('/mapel/{id}', [MasterAkademikController::class, 'destroyMapel']);

    Route::get('/plotting', [MasterAkademikController::class, 'indexPlotting']);
    Route::post('/plotting', [MasterAkademikController::class, 'storePlotting']);
    Route::delete('/plotting/{id}', [MasterAkademikController::class, 'destroyPlotting']);

    Route::get('/profil-sekolah', [MasterAkademikController::class, 'showProfilSekolah']);
    Route::put('/profil-sekolah', [MasterAkademikController::class, 'updateProfilSekolah']);

    // Konfigurasi, Jadwal Harian, & Kontrol Sesi Ujian (Urutan Rute Spesifik di Atas Rute {id})
    Route::get('/sesi-ujian', [MasterAkademikController::class, 'indexSesiUjian']);
    Route::post('/sesi-ujian', [MasterAkademikController::class, 'storeSesiUjian']);
    Route::post('/sesi-ujian/bulk-toggle', [AdminDashboardController::class, 'bulkToggleSesi']);
    Route::post('/sesi-ujian/delete-by-day', [MasterAkademikController::class, 'destroySesiByDay']);
    Route::delete('/sesi-ujian/clear-all', [MasterAkademikController::class, 'destroyAllSesiUjian']);
    Route::put('/sesi-ujian/{id}', [MasterAkademikController::class, 'updateSesiUjian']);
    Route::patch('/sesi-ujian/{id}/toggle', [MasterAkademikController::class, 'toggleSesiControls']);
    Route::delete('/sesi-ujian/{id}', [MasterAkademikController::class, 'destroySesiUjian']);

    // Import & Export Akun (Excel/CSV)
    Route::post('/murid/import', [DocumentController::class, 'importMurid']);
    Route::get('/murid/export', [DocumentController::class, 'exportMurid']);
    Route::post('/guru/import', [DocumentController::class, 'importGuru']);
    Route::get('/guru/export', [DocumentController::class, 'exportGuru']);

    // Export Rekap Nilai Global & Log Pelanggaran Global
    Route::get('/rekap-nilai/export', [DocumentController::class, 'exportRekapNilai']);
    Route::get('/log-pelanggaran/export', [DocumentController::class, 'exportLogPelanggaran']);

    // Cetak PDF Kartu Peserta & Daftar Hadir / Berita Acara
    Route::get('/cetak/kartu-peserta', [DocumentController::class, 'cetakKartuPesertaPdf']);
    Route::get('/cetak/daftar-hadir/{id_sesi}', [DocumentController::class, 'cetakDaftarHadirPdf']);
});

/*
|--------------------------------------------------------------------------
| 3. ROUTE ROLE GURU PENGAMPU
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:guru'])->prefix('guru')->group(function () {
    Route::get('/plotting-ampuan', [GuruCbtController::class, 'indexPlottingAmpuan']);
    Route::get('/plotting/{id_plotting}/bank-soal', [GuruCbtController::class, 'indexBankSoal']);
    Route::post('/plotting/{id_plotting}/bank-soal', [GuruCbtController::class, 'storeBankSoal']);
    Route::post('/plotting/{id_plotting}/bank-soal/bulk-save', [GuruCbtController::class, 'bulkSaveBankSoal']);
    Route::delete('/plotting/{id_plotting}/bank-soal/clear-all', [GuruCbtController::class, 'clearAllBankSoal']);
    Route::post('/bank-soal/upload-media', [GuruCbtController::class, 'uploadMediaOpsi']);
    Route::post('/bank-soal/{id_soal}', [GuruCbtController::class, 'updateBankSoal']);
    Route::delete('/bank-soal/{id_soal}', [GuruCbtController::class, 'destroyBankSoal']);
    Route::get('/plotting/{id_plotting}/rekap-nilai', [GuruCbtController::class, 'rekapNilaiAmpuan']);

    // Import Bank Soal via Excel/CSV & Word (.docx)
    Route::post('/plotting/{id_plotting}/bank-soal/import-excel', [DocumentController::class, 'importBankSoalExcel']);
    Route::post('/plotting/{id_plotting}/bank-soal/import-word', [DocumentController::class, 'importBankSoalWord']);

    // Export Rekap Nilai, Log Pelanggaran, & Cetak Daftar Hadir PDF Kelas Ampuan
    Route::get('/rekap-nilai/export', [DocumentController::class, 'exportRekapNilai']);
    Route::get('/log-pelanggaran/export', [DocumentController::class, 'exportLogPelanggaran']);
    Route::get('/cetak/daftar-hadir/{id_sesi}', [DocumentController::class, 'cetakDaftarHadirPdf']);
});

/*
|--------------------------------------------------------------------------
| 4. ROUTE ROLE MURID (PORTAL & MESIN UJIAN CBT)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:murid'])->prefix('murid')->group(function () {
    Route::get('/jadwal-ujian', [UjianMuridController::class, 'jadwalAktif']);
    Route::post('/sesi-ujian/{id_sesi}/mulai', [UjianMuridController::class, 'mulaiAtauResumeUjian']);
    Route::get('/ujian/{id_ujian_murid}/lembar-soal', [UjianMuridController::class, 'getLembarSoal']);
    Route::post('/ujian/{id_ujian_murid}/autosave', [UjianMuridController::class, 'autoSaveJawaban']);
    Route::post('/ujian/{id_ujian_murid}/lapor-pelanggaran', [UjianMuridController::class, 'laporPelanggaran']);
    Route::post('/ujian/{id_ujian_murid}/submit', [UjianMuridController::class, 'submitUjian']);
    Route::get('/riwayat-nilai', [UjianMuridController::class, 'riwayatNilai']);
});