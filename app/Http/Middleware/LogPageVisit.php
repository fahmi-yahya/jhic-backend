<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;

/**
 * Catat satu baris ke tabel page_visits tiap ada kunjungan baru ke
 * halaman publik (landing page). Daftarkan middleware ini HANYA di
 * route group landing page (lihat routes/web.php), jangan di route
 * admin/API supaya dashboard admin sendiri tidak ikut kehitung.
 *
 * Dedup sederhana pakai session: satu "kunjungan" cuma dihitung sekali
 * per 30 menit per browser, supaya orang yang pindah-pindah halaman di
 * web yang sama tidak bikin angkanya meledak.
 */
class LogPageVisit
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $lastVisit = $request->session()->get('last_page_visit_at');
        $now = now();

        if (!$lastVisit || $now->diffInMinutes($lastVisit) >= 30) {
            PageVisit::create([
                'path' => $request->path(),
                'ip' => $request->ip(),
                'session_id' => $request->session()->getId(),
                'visited_at' => $now,
            ]);
            $request->session()->put('last_page_visit_at', $now);
        }

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        if (!$request->isMethod('GET')) {
            return true;
        }

        // Lewati request asset (css/js/gambar) kalau route ini kebetulan
        // menangkap semuanya lewat catch-all.
        $ext = pathinfo($request->path(), PATHINFO_EXTENSION);
        if ($ext !== '') {
            return true;
        }

        // Lewati bot/crawler umum supaya tidak ikut kehitung.
        $agent = strtolower($request->userAgent() ?? '');
        foreach (['bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit'] as $needle) {
            if (str_contains($agent, $needle)) {
                return true;
            }
        }

        return false;
    }
}
