<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Auth untuk akun publik (alumni) — pakai guard "member" & tabel "alumni",
 * TERPISAH TOTAL dari AuthController (staff/admin, guard "web", tabel
 * "users"). Sengaja dua controller berbeda supaya tidak ada risiko logic
 * admin & publik kecampur atau saling pengaruh di masa depan.
 */
class MemberAuthController extends Controller
{
    private function verifyCaptcha(Request $request): void
    {
        $token = $request->input('captcha_token');

        if (!$token) {
            throw ValidationException::withMessages([
                'captcha_token' => ['Silakan centang CAPTCHA terlebih dahulu.'],
            ]);
        }

        $res = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (!$res->json('success')) {
            throw ValidationException::withMessages([
                'captcha_token' => ['Verifikasi CAPTCHA gagal atau sudah kadaluwarsa.'],
            ]);
        }
    }

    /**
     * POST /api/member/register
     * Auto-login pakai guard "member" setelah akun berhasil dibuat —
     * cocok dengan Register.jsx yang langsung redirect ke halaman utama
     * (anggapannya: sudah login) begitu register() selesai.
     */
    public function register(Request $request)
    {
        $this->verifyCaptcha($request);

        $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:150|unique:alumni,email',
            // 'confirmed' otomatis mencocokkan dengan field
            // "password_confirmation" yang dikirim Register.jsx.
            'password' => 'required|string|min:8|confirmed',
        ]);

        $alumni = Alumni::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // otomatis di-hash (cast 'hashed')
        ]);

        Auth::guard('member')->login($alumni);
        $request->session()->regenerate();

        return response()->json(['user' => $alumni], 201);
    }

    /**
     * POST /api/member/login
     */
    public function login(Request $request)
    {
        $this->verifyCaptcha($request);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('member')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json(['user' => Auth::guard('member')->user()]);
    }

    /**
     * POST /api/member/logout
     */
    public function logout(Request $request)
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * GET /api/member/me
     */
    public function me(Request $request)
    {
        return response()->json($request->user('member'));
    }
}
