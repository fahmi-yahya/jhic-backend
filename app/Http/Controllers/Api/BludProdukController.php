<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BludProduk;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BludProdukController extends Controller
{
    private const KATEGORI = ['Produk', 'Jasa'];

    // GET /api/produk
    public function index()
    {
        return response()->json(BludProduk::latest()->get());
    }

    // POST /api/produk
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori' => ['required', Rule::in(self::KATEGORI)],
            'deskripsi' => 'required|string',
            'harga' => 'nullable|string|max:120',
            'gambar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $produk = BludProduk::create($data);

        ActivityLogger::log($request, 'created', 'BludProduk', $produk->id, "Menambahkan produk/jasa \"{$produk->nama_produk}\"");

        return response()->json($produk, 201);
    }

    // DELETE /api/produk/{produk}
    public function destroy(Request $request, BludProduk $produk)
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $label = $produk->nama_produk;
        $produk->delete();

        ActivityLogger::log($request, 'deleted', 'BludProduk', $produk->id, "Menghapus produk/jasa \"{$label}\"");

        return response()->json(['message' => 'Produk/jasa dihapus.']);
    }
}
