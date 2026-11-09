<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKelas extends Model
{
    protected $table = 'master_kelas';
    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'id_jurusan',
        'tingkat',
        'kode_rombel',
        'nama_kelas',
    ];

    protected static function booted(): void
    {
        static::saving(function (MasterKelas $kelas) {
            if (empty($kelas->nama_kelas) && $kelas->jurusan) {
                $kelas->nama_kelas = trim("{$kelas->tingkat} {$kelas->jurusan->kode_jurusan} {$kelas->kode_rombel}");
            }
        });
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(MasterJurusan::class, 'id_jurusan', 'id_jurusan');
    }

    public function murid(): HasMany
    {
        return $this->hasMany(Murid::class, 'id_kelas', 'id_kelas');
    }

    public function plottingMapel(): HasMany
    {
        return $this->hasMany(PlottingMapel::class, 'id_kelas', 'id_kelas');
    }
}