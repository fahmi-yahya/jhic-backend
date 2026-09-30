<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestasi extends Model
{
    public $table = "prestasi";

    protected $fillable = [
        'nama_siswa',
        'lomba_diikuti',
        'judul_prestasi',
        'tanggal_terbit',
        'img_prestasi'
    ];
}
