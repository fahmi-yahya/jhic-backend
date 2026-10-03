<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BludPencapaian extends Model
{
    protected $table = 'blud_pencapaian';

    protected $fillable = [
        'jurusan_terkait',
        'nama_mitra',
        'deskripsi',
        'tanggal_kerjasama',
        'logo_mitra',
    ];

    protected $casts = [
        'tanggal_kerjasama' => 'date:Y-m-d',
    ];
}
