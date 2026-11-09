<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanMurid extends Model
{
    protected $table = 'jawaban_murid';
    protected $primaryKey = 'id_jawaban';

    protected $fillable = [
        'id_ujian_murid',
        'id_soal',
        'pilihan_jawaban',
        'status_ragu',
        'waktu_autosave',
    ];

    protected function casts(): array
    {
        return [
            'status_ragu' => 'boolean',
            'waktu_autosave' => 'datetime',
        ];
    }

    public function ujianMurid(): BelongsTo
    {
        return $this->belongsTo(UjianMurid::class, 'id_ujian_murid', 'id_ujian_murid');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'id_soal', 'id_soal');
    }
}