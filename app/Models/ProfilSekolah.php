<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilSekolah extends Model
{
    protected $table = 'profil_sekolah';
    protected $primaryKey = 'id_profil';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'logo_path',
        'tahun_ajaran',
        'semester',
        'nama_kepala_sekolah',
        'alamat_sekolah',
    ];

    public function sesiUjian(): HasMany
    {
        return $this->hasMany(SesiUjian::class, 'id_profil', 'id_profil');
    }
    
    /**
     * Otomatis menyesuaikan Tahun Ajaran & Semester sesuai Kalender Pendidikan Indonesia saat ini
     */
    protected static function booted(): void
    {
        static::retrieved(function (ProfilSekolah $profil) {
            $now = now();
            $tahun = $now->year;
            $bulan = $now->month; // 1..12

            if ($bulan >= 7) {
                $profil->tahun_ajaran = "{$tahun}/" . ($tahun + 1);
                $profil->semester = 'Ganjil';
            } else {
                $profil->tahun_ajaran = ($tahun - 1) . "/{$tahun}";
                $profil->semester = 'Genap';
            }
        });
    }
}