<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPelanggaran extends Model
{
    protected $table = 'log_pelanggaran';
    protected $primaryKey = 'id_log_pelanggaran';

    protected $fillable = [
        'id_ujian_murid',
        'jenis_pelanggaran',
        'waktu_pelanggaran',
    ];

    protected function casts(): array
    {
        return [
            'waktu_pelanggaran' => 'datetime',
        ];
    }

    public function ujianMurid(): BelongsTo
    {
        return $this->belongsTo(UjianMurid::class, 'id_ujian_murid', 'id_ujian_murid');
    }
}