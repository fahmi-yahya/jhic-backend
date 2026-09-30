<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lingkungan extends Model
{
    public $table = "lingkungan";

    protected $fillable = [
        'img_lingkungan',
        'nama_lingkungan',
        'deskripsi',
    ];
}
