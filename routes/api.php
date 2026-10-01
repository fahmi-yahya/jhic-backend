<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LulusanController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

// Dibatasi 5 percobaan per menit per IP untuk mencegah brute-force,
// meski captcha sudah ada sebagai lapisan pertama.
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// Publik: form kontak di landing page, TIDAK perlu login. Dibatasi rate
// biar tidak dispam.
Route::post('/pesan', [LandingPageController::class, 'storePesan'])
    ->middleware('throttle:10,1');

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
        Route::get('/pesan', [LandingPageController::class, 'indexPesan']);
    });
    Route::middleware('permission:pesan,delete')->group(function () {
        Route::delete('/pesan/{pesan}', [LandingPageController::class, 'destroyPesan']);
    });

    Route::get('/lulusan/stats', [LulusanController::class, 'stats']);
    Route::post('/lulusan/import', [LulusanController::class, 'import']);
    Route::get('/lulusan', [LulusanController::class, 'index']);

});

