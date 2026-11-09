<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankSoal extends Model
{
    protected $table = 'bank_soal';
    protected $primaryKey = 'id_soal';

    protected $fillable = [
        'id_plotting',
        'pertanyaan',
        'path_gambar',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_jawaban',
        'bobot_soal',
    ];

    protected function casts(): array
    {
        return [
            'bobot_soal' => 'integer',
        ];
    }

    public function plotting(): BelongsTo
    {
        return $this->belongsTo(PlottingMapel::class, 'id_plotting', 'id_plotting');
    }

    public function jawabanMurid(): HasMany
    {
        return $this->hasMany(JawabanMurid::class, 'id_soal', 'id_soal');
    }
}