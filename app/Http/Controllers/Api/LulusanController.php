<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lulusan;
use App\Services\LulusanImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LulusanController extends Controller
{
    /**
     * GET /api/lulusan/stats
     * GET /api/lulusan/stats?tahun=2024   (opsional, filter satu tahun)
     *
     * Balikin ringkasan 4 kategori (bekerja, wirausaha, kuliah, belum_kerja)
     * per tahun lulus + total keseluruhan. Satu lulusan bisa masuk lebih
     * dari satu kategori (lihat Lulusan::kategori()), jadi total per
     * kategori TIDAK selalu sama dengan jumlah lulusan.
     */
    public function stats(Request $request)
    {
        $query = Lulusan::select('tahun_lulus', 'status_aktifitas');
        if ($request->filled('tahun')) {
            $query->where('tahun_lulus', $request->integer('tahun'));
        }

        $perTahun = [];
        $total = ['bekerja' => 0, 'wirausaha' => 0, 'kuliah' => 0, 'belum_kerja' => 0];

        $query->get()->each(function (Lulusan $l) use (&$perTahun, &$total) {
            $tahun = $l->tahun_lulus;
            $perTahun[$tahun] ??= [
                'tahun' => $tahun,
                'total_lulusan' => 0,
                'bekerja' => 0,
                'wirausaha' => 0,
                'kuliah' => 0,
                'belum_kerja' => 0,
            ];
            $perTahun[$tahun]['total_lulusan']++;

            foreach ($l->kategori() as $kat) {
                $perTahun[$tahun][$kat]++;
                $total[$kat]++;
            }
        });

        ksort($perTahun);

        return response()->json([
            'total' => $total,
            'per_tahun' => array_values($perTahun),
        ]);
    }

    /**
     * POST /api/lulusan/import
     * multipart/form-data, field "file" berisi file .xlsx.
     * Dipanggil dari tombol upload di halaman admin (LulusanPage.jsx).
     */
    public function import(Request $request, LulusanImporter $importer)
    {
        $request->validate([
            // Laravel validasi mimes dari isi file, bukan dari ekstensi
            // nama file doang — cukup aman dari file "palsu" yang cuma
            // di-rename jadi .xlsx.
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'], // max 10MB
        ]);

        $uploaded = $request->file('file');
        // Simpan sementara dulu ke disk lokal, PhpSpreadsheet butuh path
        // file asli di server (tidak bisa baca langsung dari UploadedFile).
        //
        // PENTING: pakai Storage::path(), JANGAN rakit path manual lewat
        // storage_path('app/'.$path) — sejak Laravel 11/12, root disk
        // 'local' default-nya storage/app/private (bukan storage/app
        // lagi), jadi path manual begitu akan salah dan file-nya "tidak
        // ketemu" walau sebenarnya tersimpan.
        $path = $uploaded->store('tmp-lulusan-import');
        $fullPath = Storage::path($path);

        try {
            $hasil = $importer->import($fullPath);
        } catch (\Throwable $e) {
            @unlink($fullPath);
            return response()->json([
                'message' => 'Gagal membaca file. Pastikan formatnya sama dengan template "Tahun_Lulus_XXXX.xlsx".',
            ], 422);
        }

        @unlink($fullPath);

        return response()->json([
            'message' => "{$hasil['imported']} baris berhasil diimpor" .
                ($hasil['skipped'] > 0 ? ", {$hasil['skipped']} baris dilewati (tahun lulus kosong/tidak valid)." : '.'),
            'imported' => $hasil['imported'],
            'skipped' => $hasil['skipped'],
            'warnings' => $hasil['warnings'],
        ]);
    }

    /**
     * GET /api/lulusan?tahun=2024&status=bekerja&per_page=20
     * Daftar lulusan mentah (buat tabel detail kalau dibutuhkan), dengan
     * filter opsional per tahun dan per kategori.
     */
    public function index(Request $request)
    {
        $query = Lulusan::query();

        if ($request->filled('tahun')) {
            $query->where('tahun_lulus', $request->integer('tahun'));
        }

        if ($request->filled('status')) {
            // Filter per kategori (bekerja/wirausaha/kuliah/belum_kerja)
            // pakai pencocokan kata kunci yang sama dengan Lulusan::kategori().
            $map = [
                'bekerja' => 'Bekerja',
                'wirausaha' => 'Wirausaha',
                'kuliah' => 'Studi',
            ];
            $keyword = $map[$request->string('status')->toString()] ?? null;
            if ($keyword) {
                $query->where('status_aktifitas', 'like', "%{$keyword}%");
            } elseif ($request->string('status') === 'belum_kerja') {
                $query->where(function ($q) {
                    $q->whereNull('status_aktifitas')
                        ->orWhereIn('status_aktifitas', ['Pengangguran', 'Melakukan kegiatan lainnya']);
                });
            }
        }

        return response()->json(
            $query->orderByDesc('tahun_lulus')->paginate($request->integer('per_page', 20)),
        );
    }
}
