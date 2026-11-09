<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterJurusan extends Model
{
    protected $table = 'master_jurusan';
    protected $primaryKey = 'id_jurusan';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
    ];

    protected static function booted(): void
    {
        static::saving(function (MasterJurusan $jurusan) {
            $jurusan->kode_jurusan = strtoupper(trim($jurusan->kode_jurusan));
        });
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(MasterKelas::class, 'id_jurusan', 'id_jurusan');
    }
}