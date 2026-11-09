<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogBackupDb extends Model
{
    protected $table = 'log_backup_db';
    protected $primaryKey = 'id_backup';

    protected $fillable = [
        'id_admin',
        'nama_file_sql',
        'ukuran_file_kb',
        'waktu_backup',
    ];

    protected function casts(): array
    {
        return [
            'ukuran_file_kb' => 'float',
            'waktu_backup' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}