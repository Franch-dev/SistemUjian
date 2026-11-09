<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'username',
        'password_hash',
        'login_terakhir',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'login_terakhir' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function logBackupDb(): HasMany
    {
        return $this->hasMany(LogBackupDb::class, 'id_admin', 'id_admin');
    }

    public function sesiUjian(): HasMany
    {
        return $this->hasMany(SesiUjian::class, 'id_admin', 'id_admin');
    }
}