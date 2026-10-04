<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KisahAlumni extends Model
{
    protected $table = 'kisah_alumnis';

    protected $fillable = [
        'user_id',
        'nama',
        'jurusan',
        'tahun_lulus',
        'jabatan',
        'kisah',
        'foto',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
