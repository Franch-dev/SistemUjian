<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UjianMurid extends Model
{
    protected $table = 'ujian_murid';
    protected $primaryKey = 'id_ujian_murid';

    protected $fillable = [
        'id_sesi',
        'id_murid',
        'pakta_integritas',
        'fullscreen_lock',
        'sisa_waktu_detik',
        'jumlah_pelanggaran',
        'status_ujian',
        'waktu_mulai',
    ];

    protected function casts(): array
    {
        return [
            'pakta_integritas' => 'boolean',
            'fullscreen_lock' => 'boolean',
            'sisa_waktu_detik' => 'integer',
            'jumlah_pelanggaran' => 'integer',
            'waktu_mulai' => 'datetime',
        ];
    }

    public function sesiUjian(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'id_sesi', 'id_sesi');
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'id_murid', 'id_murid');
    }

    // Mengambil daftar Bank Soal via tabel pivot jawaban_murid (Many to Many)
    public function bankSoal(): BelongsToMany
    {
        return $this->belongsToMany(BankSoal::class, 'jawaban_murid', 'id_ujian_murid', 'id_soal')
                    ->using(JawabanMurid::class)
                    ->withPivot(['pilihan_jawaban', 'status_ragu', 'waktu_autosave'])
                    ->withTimestamps();
    }

    // Mengambil baris record jawaban_murid langsung (One to Many)
    public function daftarJawaban(): HasMany
    {
        return $this->hasMany(JawabanMurid::class, 'id_ujian_murid', 'id_ujian_murid');
    }

    // Relasi 1:1 ke Hasil Nilai Ujian
    public function hasilNilai(): HasOne
    {
        return $this->hasOne(HasilNilaiUjian::class, 'id_ujian_murid', 'id_ujian_murid');
    }

    // Relasi 1:N ke Log Pelanggaran Anti-Cheat
    public function logPelanggaran(): HasMany
    {
        return $this->hasMany(LogPelanggaran::class, 'id_ujian_murid', 'id_ujian_murid');
    }
}