<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // GET /api/activity-logs?role=&action=&page=&per_page=
    // Cuma Super Admin yang boleh lihat log aktivitas (termasuk aktivitas
    // akun admin & jurusan) — makanya tidak dipasang di grup permission
    // per-modul, cukup dicek langsung di sini.
    public function index(Request $request)
    {
        abort_unless(
            $request->user()?->role === 'superadmin',
            403,
            'Kamu tidak punya akses untuk melihat log aktivitas.',
        );

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $logs = ActivityLog::query()
            ->when($request->query('role') && $request->query('role') !== 'all', fn($q) =>
                $q->where('role', $request->query('role')))
            ->when($request->query('action') && $request->query('action') !== 'all', fn($q) =>
                $q->where('action', $request->query('action')))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($logs);
    }
}
