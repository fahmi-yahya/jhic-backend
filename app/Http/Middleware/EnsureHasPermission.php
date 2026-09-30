<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasPermission
{
    // Contoh pemakaian di route: ->middleware('permission:users,view')
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = $request->user();

        // superadmin selalu boleh akses semua modul, konsisten dengan asumsi
        // yang sudah dipakai di frontend (Sidebar.jsx & tiap halaman) —
        // jangan sampai superadmin bergantung pada ada/tidaknya baris di
        // tabel user_permissions (mis. saat modul baru ditambahkan tapi
        // belum sempat di-backfill).
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        $column = match ($action) {
            'edit' => 'can_edit',
            'delete' => 'can_delete',
            default => 'can_view',
        };

        $allowed = $user->permissions()
            ->where('module', $module)
            ->where($column, true)
            ->exists();

        if (!$allowed) {
            return response()->json(['message' => 'Kamu tidak punya akses ke aksi ini.'], 403);
        }

        return $next($request);
    }
}
