<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifikasi token Google reCAPTCHA v2 ke server Google.
 * Simpan di app/Support/Recaptcha.php (namespace sama dengan ActivityLogger).
 *
 * Gagal-aman: kalau secret belum diisi, Google tidak terjangkau, atau
 * responsnya aneh, hasilnya false (permintaan ditolak), bukan lolos.
 */
class Recaptcha
{
    public static function verify(?string $token, ?string $ip = null): bool
    {
        $secret = config('services.recaptcha.secret');

        if (!$secret || !$token) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            return $response->ok() && $response->json('success') === true;
        } catch (\Throwable $e) {
            Log::warning('Verifikasi reCAPTCHA gagal: ' . $e->getMessage());

            return false;
        }
    }
}
