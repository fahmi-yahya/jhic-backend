<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Jurusan;
use App\Models\Lingkungan;
use App\Models\Pesan;
use App\Models\Prestasi;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    // GET /api/berita
    public function indexBerita(Request $request)
    {
        $items = Berita::query()
            ->when($request->query('query'), fn($q, $s) =>
                $q->where('judul_berita', 'ilike', "%{$s}%"))
            ->latest()
            ->get();

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeBerita(Request $request)
    {
        $validated = $request->validate([
            "penulis" => "required",
            "image" => "required|image|mimes:png,jpg,jpeg,webg|max:2048",
            "judul_berita" => "required",
            "deskripsi" => "required",
            "tanggal_terbit" => "required|date_format:Y-m-d",
        ]);

        $path = $request->file('image')->store('imgBerita', 'public');
        $url = asset(Storage::url($path));

        $data = Berita::create([
            "penulis" => $validated['penulis'],
            "image" => $path,
            "judul_berita" => $validated['judul_berita'],
            "deskripsi" => $validated['deskripsi'],
            "tanggal_terbit" => $validated['tanggal_terbit'],
        ]);

        ActivityLogger::log(
            $request,
            'created',
            'Berita',
            $data->id,
            "Menambahkan berita \"{$data->judul_berita}\"",
        );

        return response()->json([
            "message" => "succes",
            "data" => $data,
            "img" => $url
        ], 200);


    }

    // DELETE /api/berita/{berita}
    public function destroyBerita(Request $request, Berita $berita)
    {
        $judul = $berita->judul_berita;
        $id = $berita->id;

        if ($berita->image) {
            Storage::disk('public')->delete($berita->image);
        }
        $berita->delete();

        ActivityLogger::log(
            $request,
            'deleted',
            'Berita',
            $id,
            "Menghapus berita \"{$judul}\"",
        );

        return response()->json(['message' => 'Berita dihapus.']);
    }

    // GET /api/jurusan
    public function indexJurusan(Request $request)
    {
        $items = Jurusan::query()
            ->when($request->query('query'), fn($q, $s) =>
                $q->where('nama_jurusan', 'ilike', "%{$s}%"))
            ->latest()
            ->get();

        return response()->json($items);
    }

    public function storeJurusan(Request $request)
    {
        $validated = $request->validate([
            "nama_jurusan" => "required",
            "img_jurusan" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "deskripsi" => "required",
        ]);

        $path = $request->file('img_jurusan')->store('foto_jurusan', 'public');

        $data = Jurusan::create([
            "nama_jurusan" => $validated['nama_jurusan'],
            "img_jurusan" => $path,
            "deskripsi" => $validated['deskripsi']
        ]);

        $url = asset(storage::url($path));

        ActivityLogger::log(
            $request,
            'created',
            'Jurusan',
            $data->id,
            "Menambahkan jurusan \"{$data->nama_jurusan}\"",
        );

        return response()->json([
            "message" => "succes",
            "data" => $data,
            "foto" => $url
        ], 200);
    }

    // DELETE /api/jurusan/{jurusan}
    public function destroyJurusan(Request $request, Jurusan $jurusan)
    {
        $nama = $jurusan->nama_jurusan;
        $id = $jurusan->id;

        if ($jurusan->img_jurusan) {
            Storage::disk('public')->delete($jurusan->img_jurusan);
        }
        $jurusan->delete();

        ActivityLogger::log(
            $request,
            'deleted',
            'Jurusan',
            $id,
            "Menghapus jurusan \"{$nama}\"",
        );

        return response()->json(['message' => 'Jurusan dihapus.']);
    }

    // GET /api/lingkungan
    public function indexLingkungan(Request $request)
    {
        $items = Lingkungan::query()
            ->when($request->query('query'), fn($q, $s) =>
                $q->where('nama_lingkungan', 'ilike', "%{$s}%"))
            ->latest()
            ->get();

        return response()->json($items);
    }

    public function storeLingkungan(Request $request)
    {
        $validated = $request->validate([
            "img_lingkungan" => "required|image|mimes:png,jpg,jpeg|max:2048",
            "deskripsi" => "required",
            "nama_lingkungan" => "required",
        ]);

        $path = $request->file('img_lingkungan')->store('img_lingkungan', 'public');

        $data = Lingkungan::create([
            'img_lingkungan' => $path,
            'nama_lingkungan' => $validated['nama_lingkungan'],
            'deskripsi' => $validated['deskripsi']
        ]);

        $url = asset(storage::url($path));

        ActivityLogger::log(
            $request,
            'created',
            'Lingkungan',
            $data->id,
            "Menambahkan data lingkungan \"{$data->nama_lingkungan}\"",
        );

        return response()->json([
            "message" => "succes",
            "data" => $data,
            "image" => $url
        ]);
    }

    // DELETE /api/lingkungan/{lingkungan}
    public function destroyLingkungan(Request $request, Lingkungan $lingkungan)
    {
        $nama = $lingkungan->nama_lingkungan;
        $id = $lingkungan->id;

        if ($lingkungan->img_lingkungan) {
            Storage::disk('public')->delete($lingkungan->img_lingkungan);
        }
        $lingkungan->delete();

        ActivityLogger::log(
            $request,
            'deleted',
            'Lingkungan',
            $id,
            "Menghapus data lingkungan \"{$nama}\"",
        );

        return response()->json(['message' => 'Data lingkungan dihapus.']);
    }

    // GET /api/prestasi
    public function indexPrestasi(Request $request)
    {
        $items = Prestasi::query()
            ->when($request->query('query'), fn($q, $s) =>
                $q->where('judul_prestasi', 'ilike', "%{$s}%"))
            ->latest()
            ->get();

        return response()->json($items);
    }

    public function storePrestasi(Request $request)
    {
        $validated = $request->validate([
            'nama_siswa' => "required",
            'lomba_diikuti' => "required",
            'judul_prestasi' => "required",
            'tanggal_terbit' => "required|date_format:Y-m-d",
            'img_prestasi' => "required|image|mimes:jpg,jpeg,png,webp|max:2048"
        ]);

        $path = $request->file('img_prestasi')->store('img_prestasi', 'public');

        $data = Prestasi::create([
            'nama_siswa' => $validated['nama_siswa'],
            'lomba_diikuti' => $validated['lomba_diikuti'],
            'judul_prestasi' => $validated['judul_prestasi'],
            'tanggal_terbit' => $validated['tanggal_terbit'],
            'img_prestasi' => $path
        ]);

        $url = asset(storage::url($path));

        ActivityLogger::log(
            $request,
            'created',
            'Prestasi',
            $data->id,
            "Menambahkan prestasi \"{$data->judul_prestasi}\"",
        );

        return response()->json([
            "message" => "succes",
            "data" => $data,
            "foto" => $url
        ], 200);
    }

    // DELETE /api/prestasi/{prestasi}
    public function destroyPrestasi(Request $request, Prestasi $prestasi)
    {
        $judul = $prestasi->judul_prestasi;
        $id = $prestasi->id;

        if ($prestasi->img_prestasi) {
            Storage::disk('public')->delete($prestasi->img_prestasi);
        }
        $prestasi->delete();

        ActivityLogger::log(
            $request,
            'deleted',
            'Prestasi',
            $id,
            "Menghapus prestasi \"{$judul}\"",
        );

        return response()->json(['message' => 'Prestasi dihapus.']);
    }

    // GET /api/pesan — hanya admin, lihat pesan masuk dari form kontak
    public function indexPesan(Request $request)
    {
        $items = Pesan::query()->latest()->get();

        return response()->json($items);
    }

    public function storePesan(Request $request)
    {
        $validated = $request->validate([
            "pesan" => "required",
            "alamat_email" => "required|email",
            "nama_lengkap" => "required|min:5"
        ]);

        $data = Pesan::create([
            "pesan" => $validated['pesan'],
            "alamat_email" => $validated['alamat_email'],
            "nama_lengkap" => $validated['nama_lengkap'],
        ]);

        // Tidak dicatat ke Activity Log — ini pesan dari pengunjung publik,
        // bukan aksi admin/jurusan yang perlu diaudit.

        return response()->json([
            "message" => "succes",
            "data" => $data
        ], 200);

    }

    // DELETE /api/pesan/{pesan}
    public function destroyPesan(Request $request, Pesan $pesan)
    {
        $nama = $pesan->nama_lengkap;
        $id = $pesan->id;
        $pesan->delete();

        ActivityLogger::log(
            $request,
            'deleted',
            'Pesan',
            $id,
            "Menghapus pesan dari \"{$nama}\"",
        );

        return response()->json(['message' => 'Pesan dihapus.']);
    }

     private function publicImageUrl(?string $path): ?string
    {
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    private function publicDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value ? (string) $value : null;
    }

    // GET /api/berita/public
    public function publicBerita()
    {
        $items = Berita::query()
            ->orderByDesc('tanggal_terbit')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn ($b) => [
                'id'             => $b->id,
                'judul_berita'   => $b->judul_berita,
                'penulis'        => $b->penulis,
                'deskripsi'      => $b->deskripsi,
                'tanggal_terbit' => $this->publicDate($b->tanggal_terbit),
                'image_url'      => $this->publicImageUrl($b->image),
            ]);

        return response()->json($items);
    }

    // GET /api/prestasi/public
    public function publicPrestasi()
    {
        $items = Prestasi::query()
            ->orderByDesc('tanggal_terbit')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'nama_siswa'     => $p->nama_siswa,
                'lomba_diikuti'  => $p->lomba_diikuti,
                'judul_prestasi' => $p->judul_prestasi,
                'tanggal_terbit' => $this->publicDate($p->tanggal_terbit),
                'image_url'      => $this->publicImageUrl($p->img_prestasi),
            ]);

        return response()->json($items);
    }

    // GET /api/jurusan/public
    public function publicJurusan()
    {
        $items = Jurusan::query()
            ->orderBy('id')
            ->get()
            ->map(fn ($j) => [
                'id'           => $j->id,
                'nama_jurusan' => $j->nama_jurusan,
                'deskripsi'    => $j->deskripsi,
                'image_url'    => $this->publicImageUrl($j->img_jurusan),
            ]);

        return response()->json($items);
    }
}
