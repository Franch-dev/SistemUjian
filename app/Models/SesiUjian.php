<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiUjian extends Model
{
    protected $table = 'sesi_ujian';
    protected $primaryKey = 'id_sesi';

    protected $fillable = [
        'id_plotting',
        'id_admin',
        'id_profil',
        'nama_sesi_ujian',
        'tanggal_ujian',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'batas_toleransi_anticheat',
        'status_sesi',
        'visibilitas_jadwal',
        'publikasi_nilai',
        'nama_pengawas_ruang',
        'catatan_berita_acara',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_ujian' => 'date:Y-m-d',
            'durasi_menit' => 'integer',
            'batas_toleransi_anticheat' => 'integer',
            'publikasi_nilai' => 'boolean',
        ];
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(PlottingMapel::class, 'id_plotting', 'id_plotting');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function profilSekolah(): BelongsTo
    {
        return $this->belongsTo(ProfilSekolah::class, 'id_profil', 'id_profil');
    }

    public function ujianMurid(): HasMany
    {
        return $this->hasMany(UjianMurid::class, 'id_sesi', 'id_sesi');
    }
}