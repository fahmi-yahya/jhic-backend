<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BludPesan;
use Illuminate\Http\Request;

class BludPesanController extends Controller
{


// Tambahkan 3 method ini ke App\Http\Controllers\LandingPageController
// (ganti versi lama jika sudah ada), dan tambahkan di bagian atas file:
//   use App\Models\Pesan;
//   use Illuminate\Http\Request;

    /**
     * PUBLIK — dipanggil form kontak di landing page.
     * Field harus sama dengan yang dikirim script.js.
     */
    public function storePesan(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'alamat_email' => ['required', 'email:rfc', 'max:150'],
            'pesan'        => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $pesan = BludPesan::create($data);

        return response()->json([
            'message' => 'Pesan berhasil terkirim.',
            'data'    => ['id' => $pesan->id],
        ], 201);
    }

    /**
     * ADMIN (permission:pesan,view) — daftar pesan masuk.
     * Query opsional: ?search=...&dibaca=0|1&per_page=15
     */
    public function indexPesan(Request $request)
    {
        $query = BludPesan::query()->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('alamat_email', 'like', "%{$search}%")
                  ->orWhere('pesan', 'like', "%{$search}%");
            });
        }

        if ($request->has('dibaca')) {
            $query->where('dibaca', $request->boolean('dibaca'));
        }

        return response()->json(
            $query->paginate((int) $request->query('per_page', 15))
        );
    }

    /**
     * ADMIN (permission:pesan,delete) — hapus pesan.
     */
    public function destroyPesan(BludPesan $pesan)
    {
        $pesan->delete();

        return response()->json(['message' => 'Pesan dihapus.']);
    }


    public function tandaiDibaca(BludPesan $pesan)
    {
        $pesan->update(['dibaca' => true]);

        return response()->json(['message' => 'Ditandai sudah dibaca.']);
    }
}
