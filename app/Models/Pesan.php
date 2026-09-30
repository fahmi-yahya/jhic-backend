<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    public $table = "pesan";

    protected $fillable = [
        "pesan",
        "alamat_email",
        "nama_lengkap"
    ];
}
