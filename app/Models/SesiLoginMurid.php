<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiLoginMurid extends Model
{
    protected $table = 'sesi_login_murid';
    protected $primaryKey = 'id_sesi_login';

    protected $fillable = [
        'id_murid',
        'session_token',
        'is_login_locked',
        'waktu_login',
    ];

    protected function casts(): array
    {
        return [
            'is_login_locked' => 'boolean',
            'waktu_login' => 'datetime',
        ];
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'id_murid', 'id_murid');
    }
}