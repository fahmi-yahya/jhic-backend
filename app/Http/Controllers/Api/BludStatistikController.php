<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BludStatistik;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class BludStatistikController extends Controller
{
    // GET /api/statistik
    public function show()
    {
        // firstOrCreate jaga-jaga kalau baris seed-nya somehow hilang —
        // halaman tetap jalan dengan nilai default 0, bukan 404/500.
        $statistik = BludStatistik::firstOrCreate(['id' => 1]);
        return response()->json($statistik);
    }

    // PUT /api/statistik
    public function update(Request $request)
    {
        $data = $request->validate([
            'jumlah_client' => 'required|integer|min:0',
            'siswa_terlibat' => 'required|integer|min:0',
            'produk_jasa' => 'required|integer|min:0',
            'jurusan_terlibat' => 'required|integer|min:0',
            'project' => 'required|integer|min:0',
        ]);

        $statistik = BludStatistik::firstOrCreate(['id' => 1]);
        $statistik->update($data);

        ActivityLogger::log($request, 'updated', 'BludStatistik', $statistik->id, 'Memperbarui angka statistik BLUD');

        return response()->json($statistik);
    }
}
