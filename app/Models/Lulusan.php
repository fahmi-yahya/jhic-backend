<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lulusan extends Model
{
    protected $fillable = [
        'nisn',
        'nama',
        'tahun_lulus',
        'komp_keahlian',
        'jenis_kelamin',
        'status_aktifitas',
        'keterangan',
        'jabatan_bekerja',
        'nama_tempat_kerja',
        'bidang_usaha',
        'nama_perguruan_tinggi',
        'nama_prodi',
    ];

    /**
     * Petakan status mentah dari excel ke 4 kategori ringkasan.
     * Satu lulusan BISA masuk lebih dari satu kategori sekaligus (mis.
     * "Melanjutkan Studi sambil Bekerja" dihitung di Kuliah DAN Bekerja)
     * — itu memang disengaja, bukan bug, karena orangnya menjalani dua2nya.
     *
     * @return string[] daftar kategori yang cocok, mis. ['bekerja'] atau
     *                   ['kuliah', 'bekerja']
     */
    public function kategori(): array
    {
        $s = $this->status_aktifitas ?? '';
        $hasil = [];

        if (str_contains($s, 'Bekerja')) {
            $hasil[] = 'bekerja';
        }
        if (str_contains($s, 'Wirausaha')) {
            $hasil[] = 'wirausaha';
        }
        if (str_contains($s, 'Studi')) {
            $hasil[] = 'kuliah';
        }
        if ($s === '' || $s === 'Pengangguran' || $s === 'Melakukan kegiatan lainnya') {
            $hasil[] = 'belum_kerja';
        }

        return $hasil;
    }
}
