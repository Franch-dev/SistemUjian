<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Guru extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'guru';
    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'kode_guru',
        'nama_lengkap',
        'nip',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guru $guru) {
            if (empty($guru->kode_guru)) {
                $cleanName = strtoupper(preg_replace('/[^a-zA-Z]/', '', $guru->nama_lengkap));
                $prefix = str_pad(substr($cleanName, 0, 6), 6, 'X');
                $guru->kode_guru = $prefix . rand(1000, 9999);
            }
        });
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function plottingMapel(): HasMany
    {
        return $this->hasMany(PlottingMapel::class, 'id_guru', 'id_guru');
    }
}