<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// Halaman Login
Route::get('/login', fn() => view('auth.login'))->name('login');

// 6 Halaman Terpisah Role Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/kelola-akun', fn() => view('admin.kelola-akun'))->name('akun');
    Route::get('/reset-sesi', fn() => view('admin.reset-sesi'))->name('reset');
    Route::get('/master-akademik', fn() => view('admin.master-akademik'))->name('master');
    Route::get('/plotting-guru', fn() => view('admin.plotting-guru'))->name('plotting');
    Route::get('/live-monitor', fn() => view('admin.live-monitor'))->name('monitor');
    Route::get('/profil', fn() => view('admin.profil'))->name('profil');
});

// Halaman Guru
Route::prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', fn() => view('guru.dashboard'))->name('dashboard');
    Route::get('/bank-soal', fn() => view('guru.bank-soal'))->name('bank-soal');
    Route::get('/daftar-hadir', fn() => view('guru.daftar-hadir'))->name('daftar-hadir');
    Route::get('/rekap-nilai', fn() => view('guru.rekap-nilai'))->name('rekap-nilai');
});

Route::prefix('murid')->name('murid.')->group(function () {
    Route::get('/dashboard', fn() => view('murid.dashboard'))->name('dashboard');
    Route::get('/riwayat-nilai', fn() => view('murid.riwayat-nilai'))->name('riwayat');
    Route::get('/ujian/{id_ujian_murid}', fn($id_ujian_murid) => view('murid.lembar-ujian', [
        'id_ujian_murid' => $id_ujian_murid
    ]))->name('ujian');
});