<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{

    public $table = "berita";
    protected $fillable = [
        "penulis",
        "image",
        "judul_berita",
        "deskripsi",
        "tanggal_terbit",
    ];
}
