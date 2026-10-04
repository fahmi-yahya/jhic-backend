<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Recaptcha;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'captcha_token' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        // Verifikasi CAPTCHA ke Google dulu, sebelum cek email/password —
        // supaya percobaan brute-force tidak sampai membebani query database.
        $googleResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $data['captcha_token'],
            'remoteip' => $request->ip(),
        ]);

        if (!$googleResponse->json('success')) {
            return response()->json([
                'message' => 'Verifikasi CAPTCHA gagal atau sudah kadaluwarsa.',
            ], 422);
        }

        // Auth::attempt() sudah otomatis cek email + password (hash) lewat
        // guard 'web'. Parameter kedua ($remember) memasang cookie
        // "remember_token" Laravel sendiri kalau true, supaya sesi tetap
        // hidup walau browser ditutup — jadi Hash::check manual tidak
        // dibutuhkan lagi. (Pastikan tabel users punya kolom remember_token,
        // ini sudah ada di migration default Laravel.)
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            // Guard sudah kadung login-in user ini — batalkan sesinya lagi
            // sebelum ditolak, supaya tidak ada sesi "aktif" untuk akun
            // yang seharusnya tidak boleh masuk.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Akun belum aktif atau sudah dinonaktifkan.'],
            ]);
        }

        $user->update(['last_active_at' => now()]);

        // Cegah session fixation: regenerate ID sesi setelah login berhasil.
        $request->session()->regenerate();

        return response()->json([
            'user' => $user->load('permissions'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('permissions'));
    }



// 1) Di bagian atas App\Http\Controllers\Api\AuthController, pastikan ada:
//      use App\Models\User;
//      use Illuminate\Http\Request;
//      use Illuminate\Support\Facades\Hash;
//
// 2) Tambahkan method ini ke dalam class AuthController:

    /**
     * POST /api/register — PUBLIK. Membuat akun baru TANPA hak akses modul.
     * Akun baru tidak langsung login; superadmin yang memberi permission
     * lewat halaman Manajemen User.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'min:3', 'max:100'],
            'email'         => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'captcha_token' => ['required', 'string'],
        ]);

        if (! Recaptcha::verify($data['captcha_token'], $request->ip())) {
            throw ValidationException::withMessages([
                'captcha_token' => 'Verifikasi CAPTCHA gagal. Silakan centang ulang lalu coba lagi.',
            ]);
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            // PENTING: isi sesuai sistem role kamu. Pakai role PALING RENDAH
            // (tanpa permission apa pun), jangan 'admin'/'superadmin'. Contoh:
            // 'role' => 'alumni',
        ]);

        Auth::guard('web')->login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Akun berhasil dibuat.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }
// 3) Di routes/api.php, di dekat route /login (di luar grup auth:sanctum):
//
//    Route::post('/register', [AuthController::class, 'register'])
//        ->middleware('throttle:5,1');
}
