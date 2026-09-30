<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    public $table = "jurusan";

    protected $fillable = [
        "nama_jurusan",
        "img_jurusan",
        "deskripsi"
    ];
}
