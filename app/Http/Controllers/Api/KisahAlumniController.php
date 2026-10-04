<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KisahAlumni;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KisahAlumniController extends Controller
{
    // true  = kisah langsung tampil di landing BKK tanpa persetujuan admin
    // false = status "pending" sampai admin menyetujui (disarankan, karena
    //         pendaftaran akun bersifat publik)
    private const AUTO_APPROVE = false;

    private const MAX_PENDING_PER_USER = 3;

    private const JURUSAN = ['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BD', 'PSPTV', 'LP'];

    // GET /api/kisah-alumni/public — PUBLIK, hanya yang sudah disetujui
    public function publicIndex()
    {
        $items = KisahAlumni::query()
            ->where('status', 'approved')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn(KisahAlumni $k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'angkatan' => "Alumni {$k->jurusan} {$k->tahun_lulus}",
                'jabatan' => $k->jabatan,
                'kisah' => $k->kisah,
                'foto_url' => $k->foto ? asset('storage/' . ltrim($k->foto, '/')) : null,
            ]);

        return response()->json($items);
    }

    // POST /api/kisah-alumni — wajib login (akun apa pun, tanpa permission modul)
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nama' => ['required', 'string', 'min:3', 'max:100'],
            'jurusan' => ['required', Rule::in(self::JURUSAN)],
            'tahun_lulus' => ['required', 'integer', 'between:1990,' . (date('Y') + 1)],
            'jabatan' => ['required', 'string', 'max:150'],
            'kisah' => ['required', 'string', 'min:30', 'max:600'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Cegah spam: batasi kisah yang masih menunggu persetujuan.
        $pending = KisahAlumni::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        if (!self::AUTO_APPROVE && $pending >= self::MAX_PENDING_PER_USER) {
            return response()->json([
                'message' => 'Kisah sebelumnya masih menunggu persetujuan admin. Mohon tunggu dulu.',
            ], 422);
        }

        $foto = $request->hasFile('foto')
            ? $request->file('foto')->store('kisah_alumni', 'public')
            : null;

        $kisah = KisahAlumni::create([
            'user_id' => $user->id,
            'nama' => $data['nama'],
            'jurusan' => $data['jurusan'],
            'tahun_lulus' => $data['tahun_lulus'],
            'jabatan' => $data['jabatan'],
            'kisah' => $data['kisah'],
            'foto' => $foto,
            'status' => self::AUTO_APPROVE ? 'approved' : 'pending',
        ]);

        return response()->json([
            'message' => self::AUTO_APPROVE
                ? 'Kisah berhasil dipublikasikan.'
                : 'Kisah terkirim dan menunggu persetujuan admin.',
            'status' => $kisah->status,
            'data' => ['id' => $kisah->id],
        ], 201);
    }

    // GET /api/kisah-alumni — ADMIN (permission bkk,view). ?status=pending|approved|rejected
    public function index(Request $request)
    {
        $items = KisahAlumni::query()
            ->with('user:id,name,email')
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json($items);
    }

    // PUT /api/kisah-alumni/{kisahAlumni}/status — ADMIN (permission bkk,edit)
    public function updateStatus(Request $request, KisahAlumni $kisahAlumni)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $kisahAlumni->update(['status' => $data['status']]);

        ActivityLogger::log(
            $request,
            'updated',
            'KisahAlumni',
            $kisahAlumni->id,
            "Mengubah status kisah alumni \"{$kisahAlumni->nama}\" menjadi {$data['status']}",
        );

        return response()->json(['message' => 'Status diperbarui.', 'status' => $kisahAlumni->status]);
    }

    // DELETE /api/kisah-alumni/{kisahAlumni} — ADMIN (permission bkk,delete)
    public function destroy(Request $request, KisahAlumni $kisahAlumni)
    {
        $nama = $kisahAlumni->nama;
        $id = $kisahAlumni->id;

        if ($kisahAlumni->foto) {
            Storage::disk('public')->delete($kisahAlumni->foto);
        }
        $kisahAlumni->delete();

        ActivityLogger::log($request, 'deleted', 'KisahAlumni', $id, "Menghapus kisah alumni \"{$nama}\"");

        return response()->json(['message' => 'Kisah alumni dihapus.']);
    }
}
