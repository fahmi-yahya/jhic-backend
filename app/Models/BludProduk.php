<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BludProduk extends Model
{
    protected $table = 'blud_produk';

    protected $fillable = [
        'nama_produk',
        'kategori',
        'deskripsi',
        'harga',
        'gambar',
    ];
}
