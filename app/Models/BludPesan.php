<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BludPesan extends Model
{
    // Tanpa ini Laravel akan mencari tabel "pesans".
    protected $table = 'blud_pesans';

    protected $fillable = [
        'nama_lengkap',
        'alamat_email',
        'pesan',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];
}
