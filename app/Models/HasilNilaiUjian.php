<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilNilaiUjian extends Model
{
    protected $table = 'hasil_nilai_ujian';
    protected $primaryKey = 'id_hasil_nilai';

    protected $fillable = [
        'id_ujian_murid',
        'jumlah_benar',
        'nilai_akhir',
        'status_kelulusan',
        'waktu_selesai',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_benar' => 'integer',
            'nilai_akhir' => 'float',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function ujianMurid(): BelongsTo
    {
        return $this->belongsTo(UjianMurid::class, 'id_ujian_murid', 'id_ujian_murid');
    }
}