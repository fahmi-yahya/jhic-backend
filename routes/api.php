<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BkkController;
use App\Http\Controllers\Api\BludPencapaianController;
use App\Http\Controllers\Api\BludPesanController;
use App\Http\Controllers\Api\BludProdukController;
use App\Http\Controllers\Api\BludStatistikController;
use App\Http\Controllers\Api\LulusanController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/statistik/public', [BludStatistikController::class, 'show'])
    ->middleware('throttle:60,1');

Route::get('/pencapaian/public', [BludPencapaianController::class, 'index'])
    ->middleware('throttle:60,1');

Route::get('/produk/public', [BludProdukController::class, 'index'])
    ->middleware('throttle:60,1');

// Publik: konten landing page (Berita, Prestasi, Jurusan). Read-only, hanya
// field yang aman ditampilkan ke pengunjung (tanpa data admin).
Route::get('/berita/public', [LandingPageController::class, 'publicBerita'])
    ->middleware('throttle:60,1');

Route::get('/prestasi/public', [LandingPageController::class, 'publicPrestasi'])
    ->middleware('throttle:60,1');

Route::get('/jurusan/public', [LandingPageController::class, 'publicJurusan'])
    ->middleware('throttle:60,1');


// Dibatasi 5 percobaan per menit per IP untuk mencegah brute-force,
// meski captcha sudah ada sebagai lapisan pertama.
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// Publik: form kontak di landing page, TIDAK perlu login. Dibatasi rate
// biar tidak dispam.
Route::post('/pesan', [BludPesanController::class, 'storePesan'])
    ->middleware('throttle:10,1');

// Publik: form pengajuan lowongan BKK dari perusahaan, TIDAK perlu login.
// Statusnya otomatis "pending" sampai di-review BK lewat endpoint di
// bawah (dalam grup auth:sanctum + permission:bkk,edit).
Route::post('/bkk', [BkkController::class, 'store'])
    ->middleware('throttle:10,1');

// Publik: daftar lowongan BKK yang SUDAH di-approve, ditampilkan di
// landing page (#lowongan) — beda dari GET /api/bkk di bawah (admin,
// wajib login + permission bkk,view) yang menampilkan SEMUA status untuk
// direview. Query "status" dipaksa "approved" di sini, apa pun yang
// dikirim caller, supaya pengunjung publik tidak bisa lihat lowongan
// pending/rejected lewat endpoint ini.
Route::get('/bkk/public', function (\Illuminate\Http\Request $request) {
    $request->merge(['status' => 'approved']);
    return app(BkkController::class)->index($request);
})->middleware('throttle:60,1');

// Publik: ringkasan statistik lulusan (Tracer Study) buat landing page.
// Aman dibuka tanpa login karena LulusanController::stats() cuma balikin
// angka agregat per kategori/tahun — TIDAK ada NISN/nama/data pribadi
// siswa di dalamnya (itu tetap di GET /api/lulusan yang terkunci login).
Route::get('/lulusan/stats/public', [LulusanController::class, 'stats'])
    ->middleware('throttle:60,1');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    Route::get('/analytics/weekly-visitors', [AnalyticsController::class, 'weeklyVisitors']);

    Route::middleware('permission:users,view')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/stats', [UserController::class, 'stats']);
    });

    Route::middleware('permission:users,edit')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::middleware('permission:users,delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    Route::middleware('permission:berita,view')->group(function () {
        Route::get('/berita', [LandingPageController::class, 'indexBerita']);
    });
    Route::middleware('permission:berita,edit')->group(function () {
        Route::post('/berita', [LandingPageController::class, 'storeBerita']);
    });
    Route::middleware('permission:berita,delete')->group(function () {
        Route::delete('/berita/{berita}', [LandingPageController::class, 'destroyBerita']);
    });

    Route::middleware('permission:jurusan,view')->group(function () {
        Route::get('/jurusan', [LandingPageController::class, 'indexJurusan']);
    });
    Route::middleware('permission:jurusan,edit')->group(function () {
        Route::post('/jurusan', [LandingPageController::class, 'storeJurusan']);
    });
    Route::middleware('permission:jurusan,delete')->group(function () {
        Route::delete('/jurusan/{jurusan}', [LandingPageController::class, 'destroyJurusan']);
    });

    Route::middleware('permission:lingkungan,view')->group(function () {
        Route::get('/lingkungan', [LandingPageController::class, 'indexLingkungan']);
    });
    Route::middleware('permission:lingkungan,edit')->group(function () {
        Route::post('/lingkungan', [LandingPageController::class, 'storeLingkungan']);
    });
    Route::middleware('permission:lingkungan,delete')->group(function () {
        Route::delete('/lingkungan/{lingkungan}', [LandingPageController::class, 'destroyLingkungan']);
    });

    Route::middleware('permission:prestasi,view')->group(function () {
        Route::get('/prestasi', [LandingPageController::class, 'indexPrestasi']);
    });
    Route::middleware('permission:prestasi,edit')->group(function () {
        Route::post('/prestasi', [LandingPageController::class, 'storePrestasi']);
    });
    Route::middleware('permission:prestasi,delete')->group(function () {
        Route::delete('/prestasi/{prestasi}', [LandingPageController::class, 'destroyPrestasi']);
    });

    Route::middleware('permission:pesan,view')->group(function () {
        Route::get('/pesan', [BludPesanController::class, 'indexPesan']);
        Route::put('/pesan/{pesan}/baca', [BludPesanController::class, 'tandaiDibaca']);
    });
    Route::middleware('permission:pesan,delete')->group(function () {
        Route::delete('/pesan/{pesan}', [BludPesanController::class, 'destroyPesan']);
    });

    // BKK — hanya BK/admin yang punya izin modul "bkk" yang bisa lihat
    // daftar pengajuan dan meng-approve/reject. storePublic sengaja di
    // LUAR grup ini (lihat di atas, tanpa auth).
    Route::middleware('permission:bkk,view')->group(function () {
        Route::get('/bkk', [BkkController::class, 'index']);
    });
    Route::middleware('permission:bkk,edit')->group(function () {
        Route::put('/bkk/{bkk}/status', [BkkController::class, 'updateStatus']);
    });
    Route::middleware('permission:bkk,delete')->group(function () {
        Route::delete('/bkk/{bkk}', [BkkController::class, 'destroy']);
    });

    // Lulusan — stats & daftar (dengan pagination) butuh izin lihat modul
    // "lulusan"; import Excel butuh izin edit. Daftarkan juga modul
    // "lulusan" ini di MODULES/ROLE_TEMPLATES management.jsx kalau mau
    // Admin/Jurusan bisa diberi akses (superadmin otomatis bisa semua).
    Route::middleware('permission:lulusan,view')->group(function () {
        Route::get('/lulusan/stats', [LulusanController::class, 'stats']);
        Route::get('/lulusan', [LulusanController::class, 'index']);
    });
    Route::middleware('permission:lulusan,edit')->group(function () {
        Route::post('/lulusan/import', [LulusanController::class, 'import']);
    });

    // BLUD — Statistik (singleton), Pencapaian/kerja sama, Produk & Jasa.
    Route::middleware('permission:statistik,view')->group(function () {
        Route::get('/statistik', [BludStatistikController::class, 'show']);
    });
    Route::middleware('permission:statistik,edit')->group(function () {
        Route::put('/statistik', [BludStatistikController::class, 'update']);
    });

    Route::middleware('permission:pencapaian,view')->group(function () {
        Route::get('/pencapaian', [BludPencapaianController::class, 'index']);
    });
    Route::middleware('permission:pencapaian,edit')->group(function () {
        Route::post('/pencapaian', [BludPencapaianController::class, 'store']);
    });
    Route::middleware('permission:pencapaian,delete')->group(function () {
        Route::delete('/pencapaian/{pencapaian}', [BludPencapaianController::class, 'destroy']);
    });

    Route::middleware('permission:produk,view')->group(function () {
        Route::get('/produk', [BludProdukController::class, 'index']);
    });
    Route::middleware('permission:produk,edit')->group(function () {
        Route::post('/produk', [BludProdukController::class, 'store']);
    });
    Route::middleware('permission:produk,delete')->group(function () {
        Route::delete('/produk/{produk}', [BludProdukController::class, 'destroy']);
    });
});
