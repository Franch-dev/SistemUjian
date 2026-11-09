<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlottingMapel extends Model
{
    protected $table = 'plotting_mapel';
    protected $primaryKey = 'id_plotting';

    protected $fillable = [
        'id_guru',
        'id_mapel',
        'id_kelas',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MasterMapel::class, 'id_mapel', 'id_mapel');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(MasterKelas::class, 'id_kelas', 'id_kelas');
    }

    public function bankSoal(): HasMany
    {
        return $this->hasMany(BankSoal::class, 'id_plotting', 'id_plotting');
    }

    public function sesiUjian(): HasMany
    {
        return $this->hasMany(SesiUjian::class, 'id_plotting', 'id_plotting');
    }
}