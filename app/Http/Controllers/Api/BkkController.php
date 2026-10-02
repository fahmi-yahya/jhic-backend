<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BkkLowongan;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BkkController extends Controller
{
    private const TIPE_PEKERJAAN = ['Tetap', 'Kontrak', 'Paruh Waktu', 'Magang', 'Freelance'];
    private const STATUS = ['pending', 'approved', 'rejected'];

    /**
     * POST /api/bkk
     * PUBLIK — diisi langsung oleh perusahaan/penyedia lowongan dari luar,
     * tanpa login. Status selalu dipaksa "pending": BK yang menentukan
     * status lewat updateStatus(), bukan dari input perusahaan.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'penanggung_jawab' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_telepon' => 'required|string|max:30',
            'alamat_perusahaan' => 'nullable|string',
            'deskripsi_perusahaan' => 'nullable|string',
            'posisi_dibutuhkan' => 'required|string|max:255',
            'tipe_pekerjaan' => ['required', Rule::in(self::TIPE_PEKERJAAN)],
            'kualifikasi' => 'required|string',
            'gaji' => 'nullable|string|max:120',
            'batas_lamar' => 'nullable|date',
        ]);

        $lowongan = BkkLowongan::create([...$data, 'status' => 'pending']);

        return response()->json($lowongan, 201);
    }

    /**
     * GET /api/bkk?status=pending|approved|rejected
     * Admin/BK — daftar semua pengajuan, bisa difilter per status.
     */
    public function index(Request $request)
    {
        $lowongan = BkkLowongan::with('reviewer:id,name')
            ->when(
                $request->query('status') && $request->query('status') !== 'all',
                fn($q) => $q->where('status', $request->query('status')),
            )
            ->latest()
            ->get();

        return response()->json($lowongan);
    }

    /**
     * PUT /api/bkk/{bkk}/status
     * BK menyetujui/menolak pengajuan setelah verifikasi perusahaannya
     * beneran ada. catatan_verifikasi opsional, dipakai internal saja
     * (tidak dikirim ke perusahaan — sesuai keputusan: cukup status di
     * sistem, tanpa notifikasi email ke perusahaan).
     */
    public function updateStatus(Request $request, BkkLowongan $bkk)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'catatan_verifikasi' => 'nullable|string',
        ]);

        $bkk->update([
            'status' => $data['status'],
            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? $bkk->catatan_verifikasi,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        ActivityLogger::log(
            $request,
            'updated',
            'BkkLowongan',
            $bkk->id,
            ($data['status'] === 'approved' ? 'Menyetujui' : 'Menolak') . " lowongan \"{$bkk->posisi_dibutuhkan}\" dari \"{$bkk->nama_perusahaan}\"",
        );

        return response()->json($bkk->fresh('reviewer:id,name'));
    }

    /**
     * DELETE /api/bkk/{bkk}
     */
    public function destroy(Request $request, BkkLowongan $bkk)
    {
        $label = "{$bkk->posisi_dibutuhkan} ({$bkk->nama_perusahaan})";
        $bkk->delete();

        ActivityLogger::log($request, 'deleted', 'BkkLowongan', $bkk->id, "Menghapus lowongan \"{$label}\"");

        return response()->json(['message' => 'Lowongan dihapus.']);
    }
}
