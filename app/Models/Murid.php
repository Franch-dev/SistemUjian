<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Murid extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'murid';
    protected $primaryKey = 'id_murid';

    protected $fillable = [
        'id_kelas',
        'nis',
        'nama_lengkap',
        'password_hash',
        'password_cetak',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(MasterKelas::class, 'id_kelas', 'id_kelas');
    }

    // Relasi 1:1 ke Sesi Login (Single Device Lock)
    public function sesiLogin(): HasOne
    {
        return $this->hasOne(SesiLoginMurid::class, 'id_murid', 'id_murid');
    }

    public function ujianMurid(): HasMany
    {
        return $this->hasMany(UjianMurid::class, 'id_murid', 'id_murid');
    }
}