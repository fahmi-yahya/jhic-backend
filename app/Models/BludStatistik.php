<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BludStatistik extends Model
{
    protected $table = 'blud_statistik';

    protected $fillable = [
        'jumlah_client',
        'siswa_terlibat',
        'produk_jasa',
        'jurusan_terlibat',
        'project',
    ];
}
