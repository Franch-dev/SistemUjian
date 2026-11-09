<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMapel extends Model
{
    protected $table = 'master_mapel';
    protected $primaryKey = 'id_mapel';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'nilai_kkm',
    ];

    protected function casts(): array
    {
        return [
            'nilai_kkm' => 'integer',
        ];
    }

    public function plottingMapel(): HasMany
    {
        return $this->hasMany(PlottingMapel::class, 'id_mapel', 'id_mapel');
    }
}