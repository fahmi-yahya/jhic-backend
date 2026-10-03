<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BludPencapaian;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BludPencapaianController extends Controller
{
    // GET /api/pencapaian
    public function index()
    {
        return response()->json(BludPencapaian::latest()->get());
    }

    // POST /api/pencapaian
    public function store(Request $request)
    {
        $data = $request->validate([
            'jurusan_terkait' => 'required|string|max:255',
            'nama_mitra' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal_kerjasama' => 'nullable|date',
            'logo_mitra' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo_mitra')) {
            $data['logo_mitra'] = $request->file('logo_mitra')->store('pencapaian', 'public');
        }

        $pencapaian = BludPencapaian::create($data);

        ActivityLogger::log(
            $request,
            'created',
            'BludPencapaian',
            $pencapaian->id,
            "Menambahkan kerja sama \"{$pencapaian->jurusan_terkait}\" x \"{$pencapaian->nama_mitra}\"",
        );

        return response()->json($pencapaian, 201);
    }

    // DELETE /api/pencapaian/{pencapaian}
    public function destroy(Request $request, BludPencapaian $pencapaian)
    {
        if ($pencapaian->logo_mitra) {
            Storage::disk('public')->delete($pencapaian->logo_mitra);
        }

        $label = "{$pencapaian->jurusan_terkait} x {$pencapaian->nama_mitra}";
        $pencapaian->delete();

        ActivityLogger::log($request, 'deleted', 'BludPencapaian', $pencapaian->id, "Menghapus kerja sama \"{$label}\"");

        return response()->json(['message' => 'Data pencapaian dihapus.']);
    }
}
