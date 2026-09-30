<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{

    /**
     * GET /api/analytics/weekly-visitors
     * Dipanggil oleh panel "Pengunjung Website" di dashboard admin.
     * Balikin 8 minggu terakhir, terurut dari yang paling lama ke
     * paling baru (biar grafiknya naik ke kanan).
     */
    public function weeklyVisitors()
    {
        // YEARWEEK() hanya ada di MySQL. Supaya query ini tetap jalan kalau
        // koneksi DB-nya Postgres (atau lainnya), grouping per-minggu ISO
        // dibuat sesuai driver yang sedang dipakai.
        $driver = DB::connection()->getDriverName();

        $yearWeekExpr = match ($driver) {
            'pgsql' => "to_char(visited_at, 'IYYY-IW')",
            'sqlite' => "strftime('%Y-%W', visited_at)",
            default => 'YEARWEEK(visited_at, 1)', // mysql / mariadb
        };

        $rows = PageVisit::select([
            DB::raw("$yearWeekExpr as yw"),
            DB::raw('MIN(visited_at) as week_start'),
            DB::raw('COUNT(*) as total'),
        ])
            ->where('visited_at', '>=', now()->subWeeks(8))
            ->groupBy('yw')
            ->orderBy('yw')
            ->get();

        $data = $rows->values()->map(function ($row, $i) {
            $start = Carbon::parse($row->week_start)->startOfWeek();
            $end = (clone $start)->endOfWeek();

            return [
                'week' => 'M' . ($i + 1),
                'range' => $start->translatedFormat('d M') . '–' . $end->translatedFormat('d M'),
                'total' => (int) $row->total,
            ];
        });

        return response()->json($data);
    }


}
