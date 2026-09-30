<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\RoleTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /api/users?query=&role=&status=&page=&per_page=
    public function index(Request $request)
    {
        // Batasi per_page (1–100) supaya orang tidak bisa minta 1 halaman
        // berisi ribuan baris lewat query string dan jadi berat lagi.
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $users = User::with('permissions')
            ->when($request->query('query'), function ($q, $search) {
                $q->where(fn($qq) => $qq
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%"));
            })
            ->when($request->query('role') && $request->query('role') !== 'all', fn($q) =>
                $q->where('role', $request->query('role')))
            ->when($request->query('status') && $request->query('status') !== 'all', fn($q) =>
                $q->where('status', $request->query('status')))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Laravel otomatis membungkus hasil paginate() jadi:
        // { data: [...], current_page, last_page, per_page, total, ... }
        return response()->json($users);
    }

    // GET /api/users/stats
    // Dipisah dari index() supaya kartu ringkasan (total/active/admin/pending)
    // tetap akurat meski tabel di-paginate — dihitung via COUNT() di database,
    // bukan dengan mengambil & menjumlahkan semua baris di PHP.
    public function stats()
    {
        return response()->json([
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'pending' => User::where('status', 'pending')->count(),
        ]);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'jurusan'])], // superadmin tidak dibuat lewat form
            'department' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['active', 'inactive', 'pending'])],
            'password' => 'required|string|min:8',
            'permissions' => 'nullable|array', // { moduleId: {view,edit,del} }
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'department' => $data['department'] ?? null,
            'status' => $data['status'],
            'require_password_reset' => true,
        ]);

        $this->syncPermissions($user, $data['permissions'] ?? RoleTemplates::forRole($data['role']));

        ActivityLogger::log(
            $request,
            'created',
            'User',
            $user->id,
            "Menambahkan akun \"{$user->name}\" ({$user->role})",
        );

        return response()->json($user->load('permissions'), 201);
    }

    // PUT /api/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'jurusan'])],
            'department' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['active', 'inactive', 'pending'])],
            'permissions' => 'nullable|array',
        ]);

        $before = $user->only(['name', 'email', 'role', 'department', 'status']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'department' => $data['department'] ?? null,
            'status' => $data['status'],
        ]);

        if (array_key_exists('permissions', $data)) {
            $this->syncPermissions($user, $data['permissions']);
        }

        $after = $user->only(['name', 'email', 'role', 'department', 'status']);
        ActivityLogger::log(
            $request,
            'updated',
            'User',
            $user->id,
            "Mengubah akun \"{$user->name}\"",
            ActivityLogger::diff($before, $after),
        );

        return response()->json($user->load('permissions'));
    }

    // DELETE /api/users/{user}
    public function destroy(Request $request, User $user)
    {
        $name = $user->name;
        $id = $user->id;
        $user->delete();

        ActivityLogger::log(
            $request,
            'deleted',
            'User',
            $id,
            "Menghapus akun \"{$name}\"",
        );

        return response()->json(['message' => 'Akun dihapus.']);
    }

    // POST /api/users/{user}/reset-password
    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => 'nullable|string|min:8',
        ]);

        $newPassword = $data['password'] ?? Str::password(12, symbols: false);

        $user->update([
            'password' => Hash::make($newPassword),
            'require_password_reset' => true,
        ]);

        ActivityLogger::log(
            $request,
            'updated',
            'User',
            $user->id,
            "Mereset password akun \"{$user->name}\"",
        );

        return response()->json([
            'message' => 'Password sementara berhasil dibuat.',
            'temporary_password' => $newPassword,
        ]);
    }

    private function syncPermissions(User $user, array $permissions): void
    {
        foreach ($permissions as $module => $perm) {
            $user->permissions()->updateOrCreate(
                ['module' => $module],
                [
                    'can_view' => $perm['view'] ?? false,
                    'can_edit' => $perm['edit'] ?? false,
                    'can_delete' => $perm['del'] ?? false,
                ]
            );
        }
    }
}
