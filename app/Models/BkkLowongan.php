<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BkkLowongan extends Model
{
    protected $table = 'bkk_lowongan';

    protected $fillable = [
        'nama_perusahaan',
        'penanggung_jawab',
        'email',
        'no_telepon',
        'alamat_perusahaan',
        'deskripsi_perusahaan',
        'posisi_dibutuhkan',
        'tipe_pekerjaan',
        'kualifikasi',
        'gaji',
        'batas_lamar',
        'status',
        'catatan_verifikasi',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'batas_lamar' => 'date:Y-m-d',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
