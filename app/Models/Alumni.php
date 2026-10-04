<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Akun publik (alumni/pengunjung) yang daftar sendiri lewat /register di
 * halaman publik — SENGAJA terpisah dari App\Models\User (staff/admin
 * dengan role & permission modul). Diautentikasi lewat guard "member"
 * (lihat config/auth.php), bukan guard "web" yang dipakai AuthController.
 */
class Alumni extends Authenticatable
{
    use Notifiable;

    protected $table = 'alumni';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
